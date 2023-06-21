<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubcategoryPrices extends Model
{
    protected $appends = [
        'period_length',
    ];

    protected $table = 'category_prices';

    public $timestamps = false;

    protected $fillable = [
        'category_id',
        'price_for_pack',
        'price_for_one_lecture',
        'period_id',
        'price_for_one_lecture_promo',
    ];

    protected $casts = [
        'price_for_one_lecture' => 'integer',
        'price_for_one_lecture_promo' => 'integer',
    ];

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
            get: fn () => $this->period->length,
        );
    }
}
