<?php

namespace App\Services;

use App\Exceptions\LoginCodeExpiredException;
use App\Mail\SendLoginCode;
use App\Models\LoginCode;
use App\Repositories\LoginCodeRepository;
use Illuminate\Support\Facades\DB;
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

    public function createAndSendEmail(string $email, int|string $code): void
    {
        DB::transaction(function () use ($email, $code) {
            LoginCode::create([
                'email' => $email,
                'code' => $code,
            ]);

            Mail::to($email)
                ->send(new SendLoginCode($code));

            Log::warning("создали, послали для $email код $code");
        });
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
