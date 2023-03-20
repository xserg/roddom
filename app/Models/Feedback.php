<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Feedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lecture_id',
        'lector_id',
        'content'
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function lector(): HasOne
    {
        return $this->hasOne(Lector::class, 'id', 'lector_id');
    }

    public function lecture(): HasOne
    {
        return $this->hasOne(Lecture::class, 'id', 'lecture_id');
    }
}
