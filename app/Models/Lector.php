<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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

    protected function rates(): Attribute
    {
        $rates = [];

        $rates['rate_avg'] = LectorRate::query()
            ->where('lector_id', '=', $this->id)
            ->average('rating');

        if (auth()->user()) {
            $rates['rate_user'] = LectorRate::query()
                ->where('lector_id', '=', $this->id)
                ->where('user_id', '=', auth()->user()->id)
                ->average('rating');
        } else {
            $rates['rate_user'] = null;
        }

        //        if ($rates) {
        //            return new Attribute(
        //                get: fn() => $rates,
        //            );
        //        }
        return new Attribute(
            get: fn () => $rates,
        );
    }
}
