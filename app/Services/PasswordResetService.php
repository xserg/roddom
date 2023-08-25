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

    public function create(string $email): PasswordReset
    {
        $code = mt_rand(100000, 999999);

        return PasswordReset::create([
            'email' => $email,
            'code' => $code,
        ]);
    }

    /**
     * @throws ResetCodeExpiredException
     */
    public function handleCodeExpiration(string|int $code): PasswordReset
    {
        $passwordReset = $this->repository->firstWhereCode($code);

        if ($this->codeIsOlderThanHour($passwordReset->created_at)) {
            $this->deleteCode($code);
            throw new ResetCodeExpiredException();
        }

        return $passwordReset;
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
