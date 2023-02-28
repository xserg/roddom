<?php

namespace App\Repositories;

use App\Models\PasswordReset;
use Illuminate\Database\Eloquent\Builder;

class PasswordResetRepository
{
    public function whereEmail($email): Builder
    {
        return PasswordReset::query()
            ->where('email', $email);
    }

    public function firstWhereCode(int|string $code): PasswordReset
    {
        return PasswordReset::query()
            ->firstWhere('code', $code);
    }
}
