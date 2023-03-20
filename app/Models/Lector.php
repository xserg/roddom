<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lector extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'position',
        'career_start',
        'photo',
        'description'
    ];

    public function lectures(): HasMany
    {
        return $this->hasMany(Lecture::class);
    }

    public function diplomas(): HasMany
    {
        return $this->hasMany(Diploma::class);
    }
}
