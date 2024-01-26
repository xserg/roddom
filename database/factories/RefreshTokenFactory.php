<?php

namespace Database\Factories;

use App\Models\RefreshToken;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;

class RefreshTokenFactory extends Factory
{
    protected $model = RefreshToken::class;

    public function definition(): array
    {
        return [
            'access_token_id' => PersonalAccessToken::inRandomOrder()->first('id')->id,
            'token' => Str::random(64),
            'expires_at' => now()->addDays(1),
        ];
    }

    public function withAccessToken(int $accessTokenId): RefreshTokenFactory
    {
        return $this->state(fn (array $attributes) => [
            'access_token_id' => $accessTokenId,
        ]);
    }

    public function withExpiresAt(DateTimeInterface $dateTime): RefreshTokenFactory
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => $dateTime,
        ]);
    }
}
