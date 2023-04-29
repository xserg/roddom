<?php

namespace App\Services;

use App\Exceptions\FailedCreateLoginCodeException;
use App\Exceptions\LoginCodeExpiredException;
use App\Models\LoginCode;
use App\Repositories\LoginCodeRepository;
use Exception;

class LoginCodeService
{
    public function __construct(
        private LoginCodeRepository $repository
    )
    {
    }

    public function deleteWhereEmail($email): bool
    {
        return $this->repository
            ->whereEmail($email)
            ->delete();
    }

    /**
     * @throws FailedCreateLoginCodeException
     */
    public function create(string $email, int|string $code): LoginCode
    {
        try {
            $loginCode = LoginCode::create([
                'email' => $email,
                'code' => $code
            ]);
        } catch (Exception) {
            throw new FailedCreateLoginCodeException();
        }

        return $loginCode;
    }

    /**
     * @throws LoginCodeExpiredException
     */
    public function throwIfExpired(string|int $code): string|int
    {
        $loginCode = $this->repository->latestWhereCode($code);

        if ($this->createdEarlierThanMinutesAgo($loginCode->created_at, 60)) {
            throw new LoginCodeExpiredException();
        }

        return $loginCode->code;
    }

    public function deleteRecordsWithCode(string|int $code): bool
    {
        $loginCode = $this->repository->allWhereCode($code);
        return $loginCode->delete();
    }

    private function createdEarlierThanMinutesAgo($createdAt, int $minutes = 60): bool
    {
        return now()->subMinutes($minutes) > $createdAt;
    }
}
