<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
    const DAY = 'day';
    const WEEK = 'week';
    const MONTH = 'month';

    protected $table = 'subscription_periods';

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
    public function categoryPrices()
    {
        return $this->hasMany(SubcategoryPrices::class);
    }
    public function promoLectures()
    {
        return $this->hasMany(SubcategoryPrices::class);
    }
}
