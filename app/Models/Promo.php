<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Promo extends Model
{
    public function subscriptions()
    {
        return $this->morphMany(
            Subscription::class,
            'subscriptions'
        );
    }

    public function subscriptionPeriodsForPromoPack(): BelongsToMany
    {
        return $this->belongsToMany(
            Period::class,
            'promo_pack_prices',
            'promo_id',
            'period_id'
        );
    }

    public function promoLectures(): BelongsToMany
    {
        return $this->belongsToMany(
            Lecture::class,
            'lectures_to_promo',
            'promo_id',
            'lecture_id'
        );
    }
}
