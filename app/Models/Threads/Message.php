<?php

namespace App\Models\Threads;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $guarded = [];
    protected $touches = ['thread'];

    protected static function booted(): void
    {
        if (auth()->id()) {
            static::saved(function (Message $message) {
                $message->thread->participants()->updateOrCreate(['user_id' => auth()->id()], ['read_at' => now()]);
            });
            static::deleted(function (Message $message) {
                $message->thread->participants()->updateOrCreate(['user_id' => auth()->id()], ['read_at' => now()]);
            });
        }
    }

    public
    function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }

    public
    function author(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
