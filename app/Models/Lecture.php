<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Lecture
 *
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string|null $preview_picture
 * @property int $video_id
 * @property int $lector_id
 * @property int $category_id
 * @property int $is_free
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\LectureCategory $category
 * @property-read \App\Models\Lector $lector
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $watchedUsers
 * @property-read int|null $watched_users_count
 * @method static \Database\Factories\LectureFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Lecture newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Lecture newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Lecture query()
 * @method static \Illuminate\Database\Eloquent\Builder|Lecture whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lecture whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lecture whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lecture whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lecture whereIsFree($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lecture whereLectorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lecture wherePreviewPicture($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lecture whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lecture whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lecture whereVideoId($value)
 * @mixin \Eloquent
 */
class Lecture extends Model
{
    use HasFactory;

    public function category(): BelongsTo
    {
        return $this->belongsTo(LectureCategory::class);
    }

    public function lector(): BelongsTo
    {
        return $this->belongsTo(Lector::class);
    }

    public function watchedUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_to_watched_lectures',
            'lecture_id',
            'user_id'
        );
    }
}
