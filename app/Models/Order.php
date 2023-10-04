<?php

namespace App\Models;

use App\Enums\PaymentStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $casts = ['exclude' => 'array'];
    protected $guarded = [];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (is_null($order->code)) {
                $order->code = Str::uuid();
            }
        });
    }

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

    public function getPeriod(): Period
    {
        return Period::firstWhere('length', $this->period);
    }
}
