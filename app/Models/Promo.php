<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Promo extends Model
{
    public function subscriptions()
    {
        return $this->morphMany(Subscription::class, 'subscriptions');
    }

    public function subscriptionPeriods(): BelongsToMany
    {
        return $this->belongsToMany(
            Period::class,
            'promo_pack_prices',
            'promo_id',
            'period_id'
        );
    }
    public function promoLecturesRelation(): BelongsToMany
    {
        return $this->belongsToMany(
            Lecture::class,
            'promo_lectures_prices',
            'promo_id',
            'lecture_id'
        );
    }

    public function promoLectures()
    {
        return $this->promoLecturesRelation->unique('id');
    }
}
