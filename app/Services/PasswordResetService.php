<?php

namespace App\Services;

use App\Exceptions\FailedCreateResetCodeException;
use App\Exceptions\ResetCodeExpiredException;
use App\Models\PasswordReset;
use App\Repositories\PasswordResetRepository;
use Exception;

class PasswordResetService
{
    public function __construct(
        private PasswordResetRepository $repository
    ) {
    }

    public function deleteWhereEmail($email): bool
    {
        return $this->repository
            ->whereEmail($email)
            ->delete();
    }

    /**
     * @throws FailedCreateResetCodeException
     */
    public function create(string $email, int|string $code): PasswordReset
    {
        try {
            $passwordReset = PasswordReset::create([
                'email' => $email,
                'code' => $code,
            ]);
        } catch (Exception) {
            throw new FailedCreateResetCodeException();
        }

        return $passwordReset;
    }

    /**
     * @throws ResetCodeExpiredException
     */
    public function checkCodeIfExpired(string|int $code): string|int
    {
        $passwordReset = $this->repository->firstWhereCode($code);

        if ($this->codeIsOlderThanHour($passwordReset->created_at)) {
            throw new ResetCodeExpiredException();
        }

        return $passwordReset->code;
    }

    public function deleteCode(string|int $code): bool
    {
        $passwordReset = $this->repository->firstWhereCode($code);

        return $passwordReset->delete();
    }

    private function codeIsOlderThanHour($createdAt): bool
    {
        return now()->subHour() > $createdAt;
    }
}
