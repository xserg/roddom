<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Sanctum\PersonalAccessToken;

class RefreshToken extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function accessToken(): BelongsTo
    {
        return $this->belongsTo(
            PersonalAccessToken::class,
            'access_token_id',
            'id'
        );
    }

    public function isExpired(): bool
    {
        return $this->expires_at < now();
    }
}
