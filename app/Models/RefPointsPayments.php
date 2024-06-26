<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefPointsPayments extends Model
{
    const REASON_BUY = 'Покупка';
    const REASON_INVITE = 'Приглашение';
    const REASON_INVITED = 'Регистрация по приглашению';

    protected $guarded = [];
    protected $casts = [
        'points' => 'integer',
        'price' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer_id');
    }
}
