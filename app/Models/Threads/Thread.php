<?php

namespace App\Models\Threads;

use App\Enums\ThreadStatusEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
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

    protected static function booted(): void
    {
        static::saved(function (Thread $thread) {
            if (auth()->id()) {
                $thread->participants()->updateOrCreate(['user_id' => auth()->id()], ['read_at' => now()]);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    public function participantsForUser(Builder $query, ?int $userId = null): HasMany
    {
        return $this
            ->participants()
            ->where('user_id', $userId);
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

    public function hasUnreadMessagesForUser(?int $userId): bool
    {
        $participant = $this->participantForUser($userId);

        return $this->hasMessages() &&
            ($this->messages->max('updated_at') > $participant?->read_at);
    }

    public function userIsNotParticipant(?int $userId = null): bool
    {
        return $this->participants->doesntContain('user_id', $userId ?? auth()->id());
    }

    public function hasMessages(): bool
    {
        return $this->messages->isNotEmpty();
    }
}
