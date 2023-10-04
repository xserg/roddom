<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FullCatalogPrices extends Model
{
    use HasFactory;

    protected $casts = [
        'is_promo' => 'boolean'
    ];

    protected $guarded = [];

    public $timestamps = false;

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    public function isPromo()
    {
        return $this->is_promo;
    }
}
