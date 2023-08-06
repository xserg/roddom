<?php

namespace App\Services;

use App\Exceptions\FailedSaveUserException;
use App\Exceptions\ResetCodeExpiredException;
use App\Exceptions\UserCannotRemoveFromSavedLectureException;
use App\Exceptions\UserCannotSaveLectureException;
use App\Exceptions\UserCannotWatchFreeLectureException;
use App\Exceptions\UserCannotWatchPaidLectureException;
use App\Jobs\AddLectureToWatchHistory;
use App\Jobs\UserDeletionRequest;
use App\Models\AppInfo;
use App\Models\Order;
use App\Models\RefInfo;
use App\Models\RefPointsPayments;
use App\Models\User;
use App\Repositories\LectureRepository;
use App\Repositories\PasswordResetRepository;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserService
{
    public function __construct(
        private PasswordResetService    $passwordResetService,
        private PasswordResetRepository $passwordResetRepository,
        private LectureService          $lectureService,
        private LectureRepository       $lectureRepository
    ) {
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
     * @throws ResetCodeExpiredException|FailedSaveUserException
     */
    public function updateUsersPassword(
        string $code,
        string $password
    ): User {
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
            AddLectureToWatchHistory::dispatch($user, $lectureId);
            return $lecture->content;

        } elseif ($lecture->isFree()) {
            if ($this->isFreeLectureAvailable($lectureId, $user)) {
                AddLectureToWatchHistory::dispatch($user, $lectureId);
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

                AddLectureToWatchHistory::dispatch($user, $lectureId);
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
        UploadedFile         $file
    ): array {
        $manager = new ImageManager(['driver' => 'imagick']);
        $image = $manager->make($file)->fit(300, 300);
        $imageSmall = $manager->make($file)->fit(150, 150);

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

    /**
     * @throws FailedSaveUserException
     * @throws NotFoundHttpException
     */
    public function deletePhoto(Authenticatable|User $user): void
    {
        if (isset($user->id)) {
            Storage::delete('images/users/' . $user->id . '.jpg');
            Storage::delete('images/users/' . $user->id . '-small' . '.jpg');

            $user->photo = null;
            $user->photo_small = null;

            $this->saveUserGuard($user);
        } else {
            throw new NotFoundHttpException('User not found');
        }
    }

    /**
     * @throws Exception
     */
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
     * @throws UserCannotRemoveFromSavedLectureException
     * @throws NotFoundHttpException
     */
    public function removeLectureFromSaved(
        int                       $lectureId,
        User|Authenticatable|null $user
    ): void {
        $lecture = $this->lectureRepository->getLectureById($lectureId);
        $alreadyRemoved = ! $user->savedLectures->contains($lectureId);

        if ($alreadyRemoved) {
            throw new UserCannotRemoveFromSavedLectureException('Лекция уже не находится в сохраненных');
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
     * @throws UserCannotRemoveFromSavedLectureException
     * @throws NotFoundHttpException
     */
    public function removeLectureFromListWatched(
        int                       $lectureId,
        User|Authenticatable|null $user
    ): void {
        $lecture = $this->lectureRepository->getLectureById($lectureId);
        $alreadyRemoved = $user->listWatchedLectures->doesntContain($lectureId);

        if ($alreadyRemoved) {
            throw new UserCannotRemoveFromSavedLectureException('Лекция уже не находится в списке просмотренных');
        }

        $user->listWatchedLectures()->detach($lectureId);
    }

    public function appendLectureCountersToUser(Model|User $user): User
    {
        $user = $user->loadCount('watchedLectures', 'savedLectures', 'listWatchedLectures');

        $subs = $user->actualSubscriptions()->with('lectures')->get();
        $purchasedLecturesIds = $subs?->map(function ($subscription) {
            return $subscription->lectures?->modelKeys();
        })->flatten()->unique();
        $purchasedLecturesCount = count($purchasedLecturesIds);
        $user->purchased_lectures_counter = $purchasedLecturesCount;

        return $user;
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

    public function rewardReferrers(Order $order, User $buyer): void
    {
        $refInfo = RefInfo::query()->first();
        $residualAmount = $order->price - $order->points;

        if ($residualAmount > 0) {
            $relationships = [
                1 => $buyer->referrer(),
                2 => $buyer->referrerSecondLevel(),
                3 => $buyer->referrerThirdLevel(),
                4 => $buyer->referrerFourthLevel(),
                5 => $buyer->referrerFifthLevel(),
            ];

            foreach ($relationships as $depth => $relationship) {
                if ($relationship->doesntExist()) {
                    continue;
                }

                $percent = $refInfo->firstWhere('depth_level', $depth)?->percent ?? 5;
                $referrer = $relationship->first();
                $pointsToGet = $residualAmount * ($percent / 100);

                $referrer->refPointsGetPayments()->create([
                    'payer_id' => $order->user->id,
                    'reason' => RefPointsPayments::REASON_BUY,
                    'ref_points' => $pointsToGet,
                    'price' => $order->price,
                    'depth_level' => $depth,
                    'percent' => $percent,
                ]);

                if ($referrer->refPoints()->exists()) {
                    $refPoints = $referrer->refPoints;
                    $refPoints->points += $pointsToGet;
                    $refPoints->save();
                } else {
                    $referrer->refPoints()->create(['points' => $pointsToGet]);
                }
            }
        }

//        adjacency-list

//        if (
//            $buyer->ancestors()
//                ->whereDepth('>', -6)
//                ->whereDepth('<', -1)
//                ->exists()
//        ) {
//            $ancestors = $buyer->ancestors()
//                ->whereDepth('>', -6)
//                ->whereDepth('<', -1)
//                ->get();
//
//            $ancestors->each(function ($ancestor) use ($order, $refInfo) {
//                $percent = $refInfo->firstWhere('depth_level', 2)->percent;
//                $residualAmount = $order->price - $order->points;
//
//                if ($residualAmount <= 0) {
//                    return;
//                }
//
//                $pointsToGet = $residualAmount * ($percent / 100);
//                $ancestor->refPointsGetPayments()->create([
//                    'payer_id' => $order->user->id,
//                    'reason' => RefPointsPayments::REASON_BUY,
//                    'ref_points' => $pointsToGet,
//                    'price' => $order->price,
//                    'depth_level' => 1,
//                    'percent' => $percent,
//                ]);
//
//                if ($ancestor->refPoints()->exists()) {
//                    $refPoints = $ancestor->refPoints;
//                    $refPoints->points += $pointsToGet;
//                    $refPoints->save();
//                } else {
//                    $ancestor->refPoints()->create(['points' => $pointsToGet]);
//                }
//            });
//        }
    }
}
