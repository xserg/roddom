<?php

namespace App\Services;

use App\Exceptions\Custom\LoginCodeExpiredException;
use App\Mail\SendLoginCode;
use App\Models\LoginCode;
use App\Repositories\LoginCodeRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LoginCodeService
{
    public function __construct(
        private LoginCodeRepository $repository
    ) {
    }

    public function deleteWhereEmail($email): bool
    {
        return $this->repository
            ->whereEmail($email)
            ->delete();
    }

    public function createAndSendEmail(string $email): void
    {
        $code = mt_rand(100000, 999999);

        $loginCode = LoginCode::create([
            'email' => $email,
            'code' => $code,
        ]);

        Mail::to($loginCode->email)
            ->send(new SendLoginCode($loginCode->code));

        Log::info("послали код $loginCode->code для юзера с почтой $loginCode->email");
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
