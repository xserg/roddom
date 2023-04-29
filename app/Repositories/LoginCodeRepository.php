<?php

namespace App\Repositories;

use App\Models\LoginCode;
use Illuminate\Database\Eloquent\Builder;

class LoginCodeRepository
{
    public function whereEmail($email): Builder
    {
        return LoginCode::query()
            ->where('email', $email);
    }

    public function latestWhereCode(int|string $code): ?LoginCode
    {
        return LoginCode::query()
            ->where('code', $code)
            ->latest('created_at')
            ->firstOrFail();
    }

    public function allWhereCode(int|string $code): Builder
    {
        return LoginCode::query()
            ->where('code', $code);
    }
}
