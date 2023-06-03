<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lector extends Model
{
    use HasFactory;

    protected $appends = [
        'a_rates'
    ];

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

    protected function aRates(): Attribute
    {
        $rates = [];

        $rates['rate_avg'] = $this
            ->rates
            ->average('rating');

        if (auth()->user()) {
            $rates['rate_user'] = $this
                ->rates
                ->where('user_id', '=', auth()->id())
                ->average('rating');
        } else {
            $rates['rate_user'] = null;
        }

        return new Attribute(
            get: fn () => $rates,
        );
    }
}
