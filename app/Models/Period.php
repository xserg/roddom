<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Period extends Model
{
    const DAY = 'day';

    const WEEK = 'week';

    const MONTH = 'month';

    protected $fillable = [
        'length',
    ];

    protected $table = 'subscription_periods';

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function categoryPrices()
    {
        return $this->hasMany(SubcategoryPrices::class);
    }

    public function promos()
    {
        return $this->hasMany(
            Promo::class,
        );
    }

    public function lectures(): BelongsToMany
    {
        return $this->belongsToMany(
            Lecture::class,
            'lectures_prices'
        )->withPivot(['id', 'price', 'lecture_id']);
    }
}
