<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LectorRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lector_id',
        'rating'
    ];

    public function lector(): BelongsTo
    {
        return $this->belongsTo(Lector::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
