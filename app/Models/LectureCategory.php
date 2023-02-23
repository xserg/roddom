<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\Models\LectureCategory
 *
 * @property int $id
 * @property int $parent_id
 * @property string $title
 * @property string|null $description
 * @property string|null $info
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read LectureCategory|null $parentCategory
 * @method static \Database\Factories\LectureCategoryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|LectureCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LectureCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LectureCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|LectureCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LectureCategory whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LectureCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LectureCategory whereInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LectureCategory whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LectureCategory whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LectureCategory whereUpdatedAt($value)
 * @property string $slug
 * @method static Builder|LectureCategory mainCategories()
 * @method static Builder|LectureCategory subCategories()
 * @method static Builder|LectureCategory whereSlug($value)
 * @mixin \Eloquent
 */
class LectureCategory extends Model
{
    use HasFactory;

    protected $table = 'lecture_categories';

    public function parentCategory(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Только НЕ подкатегории
     * @param Builder $query
     * @return void
     */
    public function scopeMainCategories(Builder $query): void
    {
        $query->where('parent_id', '=', 0);
    }

    /**
     * Только подкатегории
     * @param Builder $query
     * @return void
     */
    public function scopeSubCategories(Builder $query): void
    {
        $query->where('parent_id', '!=', 0);
    }
}
