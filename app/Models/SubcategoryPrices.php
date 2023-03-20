<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubcategoryPrices extends Model
{
    protected $appends = ['period_length'];
    protected $table = 'category_prices';

    public $timestamps = false;

    protected $fillable = [
        'category_id',
        'price_for_pack',
        'price_for_one_lecture',
        'period_id'
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    protected function periodLength(): Attribute
    {
        return new Attribute(
            get: fn() => 'цену за ' . $this->period->length . ' дня/дней',
        );
    }
}
