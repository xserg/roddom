<?php

namespace App\Models;

use App\Enums\PaymentStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'price',
        'description',
        'user_id',
        'status',
        'subscriptionable_type',
        'subscriptionable_id',
        'period',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userEmail(): string
    {
        return $this->user->email;
    }

    public function isConfirmed(): bool
    {
        return $this->status === PaymentStatusEnum::CONFIRMED->value;
    }
}
