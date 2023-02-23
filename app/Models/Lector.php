<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Lector
 *
 * @property int $id
 * @property string $name
 * @property string $career_start
 * @property string|null $photo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Diploma> $diplomas
 * @property-read int|null $diplomas_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Lecture> $lectures
 * @property-read int|null $lectures_count
 * @method static \Database\Factories\LectorFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Lector newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Lector newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Lector query()
 * @method static \Illuminate\Database\Eloquent\Builder|Lector whereCareerStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lector whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lector whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lector whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lector wherePhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lector whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Diploma> $diplomas
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Lecture> $lectures
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Diploma> $diplomas
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Lecture> $lectures
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Diploma> $diplomas
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Lecture> $lectures
 * @mixin \Eloquent
 */
class Lector extends Model
{
    use HasFactory;

    public function lectures(): HasMany
    {
        return $this->hasMany(Lecture::class);
    }

    public function diplomas(): HasMany
    {
        return $this->hasMany(Diploma::class);
    }
}
