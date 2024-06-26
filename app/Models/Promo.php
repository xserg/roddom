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

    /**
     * Общие цены промо лекций
     */
    public function subscriptionPeriodsForPromoPack(): BelongsToMany
    {
        return $this->belongsToMany(
            Period::class,
            'promo_pack_prices',
            'promo_id',
            'period_id'
        )->withPivot(['period_id', 'price', 'price_for_one_lecture']);
    }

    /**
     * Кастомные цены промо лекций
     */
    public function pricesForPromoLectures(): BelongsToMany
    {
        return $this->belongsToMany(
            Lecture::class,
            'promo_lectures_prices'
        )->withPivot(['id', 'period_id', 'price']);
    }
}
