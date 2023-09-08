<?php

namespace App\Models\Threads;

use App\Enums\ThreadStatusEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Thread extends Model
{
    use HasFactory;

    protected $casts = ['status' => ThreadStatusEnum::class];
    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    public function openedParticipant(): HasOne
    {
        return $this->hasOne(Participant::class)->where('opened', true);
    }

    public function participantForUser(int $userId): ?Participant
    {
        return $this->participants()->where('user_id', $userId)->first();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function setStatusClosed(): bool
    {
        return $this->forceFill([
            'status' => ThreadStatusEnum::CLOSED
        ])->saveQuietly();
    }

    public function isOpen(): bool
    {
        return $this->status === ThreadStatusEnum::OPEN;
    }
}
