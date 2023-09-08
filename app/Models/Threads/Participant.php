<?php

namespace App\Models\Threads;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Participant extends Model
{
    protected $guarded = [];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function setReadAtNow(): bool
    {
        return $this->forceFill([
            'read_at' => now()
        ])->save();
    }
}
