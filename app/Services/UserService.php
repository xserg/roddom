<?php

namespace App\Services;

use App\Exceptions\FailedSaveUserException;
use App\Exceptions\ResetCodeExpiredException;
use App\Exceptions\UserCannotWatchFreeLectureException;
use App\Exceptions\UserCannotWatchPaidLectureException;
use App\Jobs\UserDeletionRequest;
use App\Models\PasswordReset;
use App\Models\User;
use App\Repositories\LectureRepository;
use App\Repositories\PasswordResetRepository;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserService
{
    public function __construct(
        private PasswordResetService    $passwordResetService,
        private PasswordResetRepository $passwordResetRepository,
        private LectureService          $lectureService,
        private LectureRepository       $lectureRepository
    )
    {
    }

    public function getUserByEmail($email): User
    {
        return User::firstWhere('email', '=', $email);
    }

    /**
     * @throws FailedSaveUserException
     */
    public function create(array $attributes): User
    {
        $attributes['password'] = Hash::make($attributes['password']);

        $user = new User($attributes);

        $this->saveUserGuard($user);

        return $user;
    }

    /**
     * @throws FailedSaveUserException
     */
    public function makeDeletionRequest(User $currentUser): void
    {
        $currentUser->to_delete = true;

        $this->saveUserGuard($currentUser);

        UserDeletionRequest::dispatch($currentUser);
    }

    /**
     * @throws FailedSaveUserException
     */
    private function saveUserGuard(User $user): void
    {
        if (!$user->save()) {
            throw new FailedSaveUserException();
        }
    }

    /**
     * @throws ResetCodeExpiredException|FailedSaveUserException
     */
    public function updateUsersPassword(
        string $code,
        string $password
    ): User
    {
        $passwordReset = $this
            ->passwordResetRepository
            ->firstWhereCode($code);

        try {
            $this->passwordResetService
                ->checkCodeIfExpired($passwordReset->code);

        } catch (ResetCodeExpiredException) {

            $this->passwordResetService
                ->deleteCode($code);

            throw new ResetCodeExpiredException();
        }

        $user = $this->getUserByEmail($passwordReset->email);

        $user->password = Hash::make($password);
        $this->saveUserGuard($user);

        $passwordReset->delete();

        return $user;
    }

    private function codeIsOlderThanHour($createdAt): bool
    {
        return now()->subHour() > $createdAt;
    }


    /**
     * @throws Exception
     */
    private function userAuthorizedGuard(int $id, User $currentUser): void
    {
        if ($currentUser->id !== $id) {
            throw new Exception('This can only be done by the same user.');
        }
    }

    public function addLectureToWatched(
        int  $lectureId,
        User $user,
        bool $setAvailableUntil = false
    ): void
    {
        if ($setAvailableUntil) {
            $user->watchedLectures()->attach($lectureId, ['available_until' => now()->addHours(24)]);
        } else {
            $user->watchedLectures()->attach($lectureId);
        }
    }

    /**
     * @throws FailedSaveUserException
     * @throws UserCannotWatchFreeLectureException
     * @throws UserCannotWatchPaidLectureException
     */
    public function watchLecture(
        int                  $lectureId,
        Authenticatable|User $user
    ): int
    {
        $lecture = $this->lectureRepository->getLectureById($lectureId);

        if (!$lecture) {
            throw new NotFoundHttpException('Лекция с id ' . $lectureId . ' не найдена');
        }

        if ($this->lectureService->isFree($lectureId)) {
            if ($this->isFreeLectureAvailable($lectureId, $user)) {
                return $this->lectureRepository->getLectureById($lectureId)->video_id;
            }

            if ($this->userCanWatchNewFreeLecture($user)) {
                $user->watchedLectures()->detach($lectureId);
                $this->addLectureToWatched($lectureId, $user, true);

                $user = $this->setFreeLectureWatchedNow($user);
                $this->saveUserGuard($user);
                return $this->lectureRepository->getLectureById($lectureId)->video_id;
            }

            throw new UserCannotWatchFreeLectureException(
                'Пользователь не сможет посмотреть новую бесплатную лекцию'
            );
        } else {
            if ($this->isLecturePurchased($lectureId, $user)) {
                $user->watchedLectures()->detach($lectureId);
                $this->addLectureToWatched($lectureId, $user);
                return $this->lectureRepository->getLectureById($lectureId)->video_id;
            }

            throw new UserCannotWatchPaidLectureException('Пользователь не сможет посмотреть платную лекцию');
        }
    }

    /**
     * @throws Exception
     */
    public function saveUsersPhoto(
        Authenticatable $user,
        UploadedFile    $file): array
    {
        // TODO: refactoring
        $manager = new ImageManager(['driver' => 'imagick']);
        $image = $manager->make($file)->resize(300, 300);
        $imageSmall = $manager->make($file)->resize(150, 150);

        $dirCreated = Storage::makeDirectory('images/' . $user->id);

        if (!$dirCreated) {
            throw new Exception('Directory could not be created');
        }

        // /app/public/images/{user-id}/{user-id}.{extension}
        // linked folder -> /public/storage/images/{user-id}/{user-id}.{extension}
        // url . /storage/images/1/1.jpg
        $path = "/images/$user->id/$user->id.jpg";
        $smallImagePath = "/images/$user->id/$user->id-small.jpg";

        if (!$image->save(storage_path('app/public' . $path), format: 'jpg') ||
            !$imageSmall->save(storage_path('app/public' . $smallImagePath), format: 'jpg')) {
            throw new Exception('Could not upload image');
        }

        $user->photo = config('app.url') . '/storage' . $path;
        $user->photo_small = config('app.url') . '/storage' . $smallImagePath;

        if (!$user->save()) {
            throw new Exception('Could not save user in database');
        }

        return [$user->photo, $user->photo_small];
    }

    /**
     * @throws Exception
     */
    public function saveProfile($user, array $profile)
    {
        $user->fill($profile);

        if ($pregnancy_weeks = $profile['pregnancy_weeks']) {
            $user->pregnancy_start = Carbon::now()
                ->subWeeks($pregnancy_weeks)
                ->toDateString();
        }

        $this->saveUserGuard($user);

        return $user;
    }

    private function isFreeLectureAvailable(int $lectureId, User $user): bool
    {
        $lecture = $user
            ->watchedLectures
            ->firstWhere('id', $lectureId);

        if (!$lecture) {
            return false;
        }

        $availableUntil = $lecture->pivot->available_until;

        $available = $availableUntil > now();

        return $available;
    }

    public function userCanWatchNewFreeLecture(User|Authenticatable $user): bool
    {
        return
            $user->free_lecture_watched < now()->subHours(24) ||
            is_null($user->free_lecture_watched);
    }

    public function setFreeLectureWatchedNow(User|Authenticatable $user): User
    {
        $user->free_lecture_watched = now();
        return $user;
    }

    public function isLecturePurchased(int $lectureId, User|Authenticatable $user): bool
    {
        return
            $this->lectureService->isLectureStrictPurchased($lectureId, $user) ||
            $this->lectureService->isLecturesCategoryPurchased($lectureId, $user) ||
            $this->lectureService->isLecturesPromoPurchased($lectureId, $user);
    }
}
