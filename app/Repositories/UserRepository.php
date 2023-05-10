<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class UserRepository
{
    public function findByEmail(string $email): Model|User
    {
        $user = User::query()
            ->where(['email' => $email])
            ->firstOrFail();

        return $user;
    }

    public function promoSubscriptions(): ?Collection
    {
        if (auth()->user()) {
            return auth()->user()
                ->promoSubscriptions()
                ->get();
        }

        return null;
    }

    public function categorySubscriptions(): ?Collection
    {
        if (auth()->user()) {
            return auth()->user()
                ->categorySubscriptions()
                ->get();
        }

        return null;
    }

    public function lectureSubscriptions(): ?Collection
    {
        if (auth()->user()) {
            return auth()->user()
                ->lectureSubscriptions()
                ->get();
        }

        return null;
    }
}
