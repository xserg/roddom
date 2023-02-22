<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Diploma
 *
 * @property int $id
 * @property string $preview_picture
 * @property int $lector_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Lector $lector
 * @method static \Database\Factories\DiplomaFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Diploma newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Diploma newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Diploma query()
 * @method static \Illuminate\Database\Eloquent\Builder|Diploma whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Diploma whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Diploma whereLectorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Diploma wherePreviewPicture($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Diploma whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Diploma extends Model
{
    use HasFactory;

    protected $table = 'diplomas';

    public function lector(): BelongsTo
    {
        return $this->belongsTo(Lector::class);
    }
}
