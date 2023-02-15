<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Diploma extends Model
{
    use HasFactory;

    protected $table = 'diplomas';

    public function lector(): BelongsTo
    {
        return $this->belongsTo(Lector::class);
    }
}
