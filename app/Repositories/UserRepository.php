<?php

namespace App\Repositories;

use App\Models\Lecture;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserRepository
{
    public function findByEmail(array $email): Model|User
    {
        $user = User::query()
            ->where(['email' => $email])
            ->firstOrFail();

        return $user;
    }

    public function getLectureById($id)
    {
        $lecture = Lecture::query()
            ->with('lector')
            ->where(['id' => $id])
            ->first();

        return $lecture;
    }
}
