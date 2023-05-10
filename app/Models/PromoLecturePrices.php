<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromoLecturePrices extends Model
{
    protected $appends = ['period_length'];

    protected $table = 'promo_lectures_prices';

    public $timestamps = false;

    protected $fillable = [
        'lecture_id',
        'price',
        'period_id',
        'promo_id',
    ];

    protected function periodLength(): Attribute
    {
        return new Attribute(
            get: fn () => 'цену за '.$this->period->length.' дня/дней',
        );
    }

    public function lecture(): BelongsTo
    {
        return $this->belongsTo(Lecture::class);
    }

    public function promo(): BelongsTo
    {
        return $this->belongsTo(Promo::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }
}
