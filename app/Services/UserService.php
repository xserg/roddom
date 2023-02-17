<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Response;

class UserService
{
    public function create(array $attributes): User
    {
        $user = new User($attributes);

        if (! $user->save()) {
            abort(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                "Couldn't save user to database"
            );
        }

        return $user;
    }
}
