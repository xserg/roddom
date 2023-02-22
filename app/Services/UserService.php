<?php

namespace App\Services;

use App\Jobs\UserDeletionRequest;
use App\Models\User;
use Exception;

class UserService
{
    /**
     * @throws Exception
     */
    public function create(array $attributes): User
    {
        $user = new User($attributes);

        $this->saveUserGuard($user);

        return $user;
    }

    /**
     * @throws Exception
     */
    public function makeDeletionRequest($id): void
    {
        $this->userAuthorizedGuard($id);

        $user = auth()->user();
        $user->to_delete = true;

        $this->saveUserGuard($user);

        UserDeletionRequest::dispatch();
    }

    /**
     * @throws Exception
     */
    private function saveUserGuard($user): void
    {
        if (!$user->save()) {
            throw new Exception('Couldn\'t save user to database');
        }
    }

    /**
     * @throws Exception
     */
    private function userAuthorizedGuard($id): void
    {
        $user = auth()->user();

        if ($user->id !== $id) {
            throw new Exception('This can only be done by the same user.');
        }
    }

    public function addLectureToWatched(
        int  $lectureId,
        User $currentUser
    ): void
    {
        /**
         * @var $watchedLectures Collection
         */
        $watchedLectures = $currentUser->watchedLectures;
        $lectureAlreadyInWatched = $watchedLectures->keyBy('id')->has($lectureId);

        if ($lectureAlreadyInWatched) {
            return;
        }

        $currentUser->watchedLectures()->attach($lectureId);
    }

    public function canUserWatchLecture(
        int             $lectureId,
        Authenticatable $currentUser
    ): bool
    {
        return true;
    }

    /**
     * @throws Exception
     */
    public function saveUsersPhoto(
        Authenticatable $user,
        UploadedFile    $file): string
    {
        $extension = $file->extension();
        $filename = "$user->id.$extension";

        // /app/public/images/{user-id}.{extension}
        // linked folder -> /public/storage/images/{user-id}.{extension}
        $path = 'storage/' . $file->storeAs('images', $filename);
        $user->photo = $path;

        if(! $user->save()){
            throw new Exception('Could not save user in database');
        }

        return $path;
    }
}
