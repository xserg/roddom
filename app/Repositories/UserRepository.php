<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class UserRepository
{
    public function getUserById(int $userId, $with = []): User
    {
        return User::with($with)->findOrFail($userId);
    }

    public function findByEmail(string $email, $with = []): Model|User
    {
        $user = User::query()
            ->with($with)
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
