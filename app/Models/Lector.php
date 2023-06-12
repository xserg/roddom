<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lector extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'position',
        'career_start',
        'photo',
        'description',
    ];

    public function lectures(): HasMany
    {
        return $this->hasMany(Lecture::class);
    }

    public function diplomas(): HasMany
    {
        return $this->hasMany(Diploma::class);
    }

    public function rates(): HasMany
    {
        return $this->hasMany(LectorRate::class);
    }
    public function ratesWhereUser(int $userId): HasMany
    {
        return $this->rates()->where('user_id', $userId);
    }

    public function averageRate(): HasOne
    {
        return $this->hasOne(LectorAverageRate::class);
    }
}
