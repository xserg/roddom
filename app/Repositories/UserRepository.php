<?php

namespace App\Repositories;

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
}
