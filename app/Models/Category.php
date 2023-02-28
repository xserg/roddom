<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $table = 'lecture_categories';

    public function parentCategory(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function lectures(): HasMany
    {
        return $this->hasMany(Lecture::class);
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
