<?php

namespace App\Models\Threads;

use App\Enums\ThreadStatusEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Thread extends Model
{
    use HasFactory;

    protected $casts = ['status' => ThreadStatusEnum::class];
    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function setStatusClosed(): bool
    {
        return $this->forceFill([
            'status' => ThreadStatusEnum::CLOSED
        ])->save();
    }

    public function isOpen(): bool
    {
        return $this->status === ThreadStatusEnum::OPEN;
    }
}
