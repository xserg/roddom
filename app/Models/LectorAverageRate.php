<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LectorAverageRate extends Model
{
    protected $guarded = [];
    protected $table = 'average_lector_rates';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'lector_id';

    public function lector(): BelongsTo
    {
        return $this->belongsTo(Lector::class);
    }
}
