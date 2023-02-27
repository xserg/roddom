<?php

namespace App\Services;

use App\Jobs\UserDeletionRequest;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;

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
    public function makeDeletionRequest(User $currentUser): void
    {
        $currentUser->to_delete = true;

        $this->saveUserGuard($currentUser);

        UserDeletionRequest::dispatch($currentUser);
    }

    /**
     * @throws Exception
     */
    private function saveUserGuard(User $user): void
    {
        if (!$user->save()) {
            throw new Exception('Couldn\'t save user to database');
        }
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

        $user->photo = env('APP_URL') . '/storage' . $path;
        $user->photo_small = env('APP_URL') . '/storage' . $smallImagePath;

        if (!$user->save()) {
            throw new Exception('Could not save user in database');
        }

        return [$user->photo, $user->photo_small];
    }
}
