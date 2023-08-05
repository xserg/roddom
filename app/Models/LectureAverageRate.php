<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LectureAverageRate extends Model
{
    protected $guarded = [];
    protected $table = 'average_lecture_rates';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'lecture_id';

    public function lecture(): BelongsTo
    {
        return $this->belongsTo(Lecture::class);
    }
}
