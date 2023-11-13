<?php

namespace App\Services;

use App\Exceptions\Custom\FailedSaveUserException;
use App\Exceptions\Custom\UserCannotRemoveLectureFromListException;
use App\Exceptions\Custom\UserCannotSaveLectureException;
use App\Exceptions\Custom\UserCannotWatchFreeLectureException;
use App\Exceptions\Custom\UserCannotWatchPaidLectureException;
use App\Jobs\AddLectureToWatchHistory;
use App\Jobs\UserDeletionRequest;
use App\Models\AppInfo;
use App\Models\Order;
use App\Models\RefInfo;
use App\Models\RefPointsGainOnce;
use App\Models\RefPointsPayments;
use App\Models\User;
use App\Repositories\LectureRepository;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserService
{
    public function __construct(
        private LectureService    $lectureService,
        private LectureRepository $lectureRepository,
        private ImageManager      $imageManager
    ) {
    }

    public function getUserByEmail($email): User
    {
        return User::query()->where('email', $email)->firstOrFail();
    }

    /**
     * @throws FailedSaveUserException
     */
    public function create(array $attributes): User
    {
        $attributes['password'] = Hash::make($attributes['password']);

        $refToken = Str::uuid();
        $attributes['ref_token'] = $refToken;

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
    public function updatePassword(int $userId, string $password): User
    {
        $user = User::findOrFail($userId);
        $user->password = Hash::make($password);
        $this->saveUserGuard($user);

        return $user;
    }

    /**
     * @throws FailedSaveUserException
     * @throws UserCannotWatchFreeLectureException
     * @throws UserCannotWatchPaidLectureException
     */
    public function watchLecture(
        int                  $lectureId,
        Authenticatable|User $user
    ): string {
        $lecture = $this->lectureRepository->getLectureById($lectureId);

        if ($this->lectureService->isLecturePurchased($lectureId)) {
            AddLectureToWatchHistory::dispatch($user, $lectureId, now());
            return $lecture->content;

        } elseif ($lecture->isFree()) {
            if ($this->isFreeLectureAvailable($lectureId, $user)) {
                AddLectureToWatchHistory::dispatch($user, $lectureId, now());
                return $lecture->content;
            }

            if ($this->userCanWatchNewFreeLecture($user)) {
                $hoursAvailableToWatch = AppInfo::query()->first()?->free_lecture_hours ?? 24;

                $user->freeWatchedLectures()->syncWithoutDetaching(
                    [$lectureId => ['available_until' => now()->addHours($hoursAvailableToWatch)]]
                );

                if (! $user->markNextFreeLectureAvailable($hoursAvailableToWatch)) {
                    throw new FailedSaveUserException();
                }

                AddLectureToWatchHistory::dispatch($user, $lectureId, now());
                return $lecture->content;
            }

            throw new UserCannotWatchFreeLectureException(
                'Пользователь не сможет посмотреть новую бесплатную лекцию'
            );
        }

        throw new UserCannotWatchPaidLectureException('Пользователь не сможет посмотреть платную лекцию');
    }

    /**
     * @throws Exception
     */
    public function saveUsersPhoto(
        Authenticatable|User $user,
        UploadedFile         $file,
    ): array {
        $image = $this->imageManager->make($file)->fit(300, 300);
        $imageSmall = $this->imageManager->make($file)->fit(150, 150);

        $dirCreated = Storage::makeDirectory('images/users/');

        if (! $dirCreated) {
            throw new Exception('Directory could not be created');
        }

        if (
            ! $image->save(storage_path('app/public/images/users/' . $user->id . '.jpg')) ||
            ! $imageSmall->save(storage_path('app/public/images/users/' . $user->id . '-small' . '.jpg'))
        ) {
            throw new Exception('Could not upload image');
        }

        $user->photo = 'images/users/' . $user->id . '.jpg';
        $user->photo_small = 'images/users/' . $user->id . '-small' . '.jpg';

        if (! $user->save()) {
            throw new Exception('Could not save user in database');
        }

        return [$user->photo, $user->photo_small];
    }

    public function deletePhoto(int $userId): void
    {
        $user = User::findOrFail($userId);

        Storage::delete('images/users/' . $user->id . '.jpg');
        Storage::delete('images/users/' . $user->id . '-small' . '.jpg');

        $user->photo = null;
        $user->photo_small = null;

        $this->saveUserGuard($user);
    }

    public function saveProfile($user, array $profile): User
    {
        $user->fill($profile);

        if (isset($profile['pregnancy_weeks'])) {
            $pregnancy_weeks = $profile['pregnancy_weeks'];
            $user->pregnancy_start = Carbon::now()
                ->subWeeks($pregnancy_weeks)
                ->toDateString();
        }

        if (! $user->isProfileFulfilled()) {
            $user->setProfileFulfilled();
        }

        $this->saveUserGuard($user);

        return $user;
    }

    public function userCanWatchNewFreeLecture(User|Authenticatable $user): bool
    {
        return
            $user->next_free_lecture_available < now() ||
            is_null($user->next_free_lecture_available);
    }

    /**
     * @throws UserCannotSaveLectureException
     * @throws NotFoundHttpException
     */
    public function addLectureToSaved(
        int                  $lectureId,
        User|Authenticatable $user
    ): void {
        $lecture = $this->lectureRepository->getLectureById($lectureId);
        $alreadySaved = $user->savedLectures->contains($lectureId);

        if ($alreadySaved) {
            throw new UserCannotSaveLectureException('Лекция c id ' . $lectureId . ' уже в сохраненных');
        }

        $user->savedLectures()->attach($lectureId);
    }

    /**
     * @throws UserCannotRemoveLectureFromListException
     * @throws NotFoundHttpException
     */
    public function removeLectureFromSaved(
        int                       $lectureId,
        User|Authenticatable|null $user
    ): void {
        $lecture = $this->lectureRepository->getLectureById($lectureId);
        $alreadyRemoved = ! $user->savedLectures->contains($lectureId);

        if ($alreadyRemoved) {
            throw new UserCannotRemoveLectureFromListException('Лекция уже не находится в сохраненных');
        }

        $user->savedLectures()->detach($lectureId);
    }

    /**
     * @throws UserCannotSaveLectureException
     * @throws NotFoundHttpException
     */
    public function addLectureToListWatched(
        int                  $lectureId,
        User|Authenticatable $user
    ): void {
        $lecture = $this->lectureRepository->getLectureById($lectureId);
        $alreadySaved = $user->listWatchedLectures->contains($lectureId);

        if ($alreadySaved) {
            throw new UserCannotSaveLectureException('Лекция c id ' . $lectureId . ' уже в списке просмотренных');
        }

        $user->listWatchedLectures()->attach($lectureId);
    }

    /**
     * @throws UserCannotRemoveLectureFromListException
     * @throws NotFoundHttpException
     */
    public function removeLectureFromListWatched(int $lectureId, User $user): void
    {
        $lecture = $this->lectureRepository->getLectureById($lectureId);
        $alreadyRemoved = $user->listWatchedLectures->doesntContain($lectureId);

        if ($alreadyRemoved) {
            throw new UserCannotRemoveLectureFromListException('Лекция уже не находится в списке просмотренных');
        }

        $user->listWatchedLectures()->detach($lectureId);
    }

    public function appendLectureCountersToUser(User $user): User
    {
        return $user
            ->loadCount('watchedLectures', 'savedLectures', 'listWatchedLectures')
            ->append('purchasedLecturesCounter');
    }

    public function createToken(Model|User $user, ?string $deviceName = 'default_device')
    {
        $token = $user->tokens()->firstWhere('name', $deviceName);

        if (! $token && $user->tokens()->count() >= 3) {
            $token = $user->tokens()
                ->orderBy('created_at')
                ->first();
        }

        $token?->delete();
        return $user->createToken($deviceName)->plainTextToken;
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

    private function isFreeLectureAvailable(int $lectureId, User $user): bool
    {
        $lecture = $user
            ->freeWatchedLectures()
            ->firstWhere('lecture_id', $lectureId);

        if (! $lecture) {
            return false;
        }

        $availableUntil = $lecture->pivot->available_until;

        $available = $availableUntil > now();

        return $available;
    }

    /**
     * @throws FailedSaveUserException
     */
    private function saveUserGuard(User $user): void
    {
        if (! $user->save()) {
            throw new FailedSaveUserException();
        }
    }

    public function rewardForRefLinkRegistration(User $user): void
    {
        if ($user->hasNotReferrer()) {
            return;
        }

        $referrer = $user->referrer;

        if ($referrer->canGetReferrersBonus()) {

            $pointsToGet = RefPointsGainOnce::query()->firstWhere('user_type', 'referrer')?->points_gains ?? 0;
            $referrer->refPointsGetPayments()->create([
                'payer_id' => $user->id,
                'ref_points' => $pointsToGet,
                'reason' => RefPointsPayments::REASON_INVITE
            ]);

            if ($referrer->refPoints()->exists()) {
                $referrer->refPoints->points += $pointsToGet;
                $referrer->refPoints->save();
            } else {
                $referrer->refPoints()->create(['points' => $pointsToGet]);
            }

            $referrer->markCantGetReferrersBonus();
        }

        if ($user->canGetReferralsBonus()) {
            $pointsToGet = RefPointsGainOnce::query()->firstWhere('user_type', 'referral')?->points_gains ?? 0;
            if ($user->refPoints()->exists()) {
                $user->refPoints->points += $pointsToGet;
                $user->refPoints->save();
            } else {
                $user->refPoints()->create(['points' => $pointsToGet]);
            }

            $user->refPointsGetPayments()->create([
                'payer_id' => $referrer->id,
                'ref_points' => $pointsToGet,
                'reason' => RefPointsPayments::REASON_INVITED
            ]);

            $user->markCantGetReferralsBonus();
            $user->refresh();
        }
    }
}
