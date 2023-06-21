<?php

namespace App\Models;

use App\Traits\MoneyConversion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Category extends Model
{
    use HasFactory, MoneyConversion;

    protected $appends = [
        'parent_slug',
    ];

    protected $fillable = [
        'parent_id',
        'title',
        'description',
        'info',
        'slug',
        'preview_picture',
        'is_promo',
    ];

    protected $casts = [
        'is_promo' => 'bool'
    ];

    protected $table = 'lecture_categories';

    protected static function booted(): void
    {
        static::created(function (Category $category) {
            if ($category->categoryPrices->isNotEmpty()) {
                return;
            }

            $categoryPricesDay = new SubcategoryPrices(
                [
                    'category_id' => $category->id,
                    'price_for_pack' => null,
                    'price_for_one_lecture' => 10000,
                    'price_for_one_lecture_promo' => 9000,
                    'period_id' => 1,
                ]
            );
            $categoryPricesDay->save();

            $categoryPricesWeek = new SubcategoryPrices(
                [
                    'category_id' => $category->id,
                    'price_for_pack' => null,
                    'price_for_one_lecture' => 20000,
                    'price_for_one_lecture_promo' => 15000,
                    'period_id' => 2,
                ]
            );
            $categoryPricesWeek->save();

            $categoryPricesMonth = new SubcategoryPrices(
                [
                    'category_id' => $category->id,
                    'price_for_pack' => null,
                    'price_for_one_lecture' => 50000,
                    'price_for_one_lecture_promo' => 40000,
                    'period_id' => 3,
                ]
            );
            $categoryPricesMonth->save();
        });
    }

    public function parentCategory(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function childrenCategories(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function lectures(): HasMany
    {
        return $this->hasMany(Lecture::class);
    }

    public function childrenCategoriesLectures(): HasManyThrough
    {
        return $this->hasManyThrough(
            Lecture::class,
            self::class,
            'parent_id',
            'category_id');
    }

    public function categoryPrices(): HasMany
    {
        return $this->hasMany(SubcategoryPrices::class);
    }

    public function subscriptions(): MorphMany
    {
        return $this->morphMany(Subscription::class, 'subscriptions');
    }

    /**
     * Только НЕ подкатегории
     */
    public function scopeMainCategories(Builder $query): void
    {
        $query->where('parent_id', '=', 0);
    }

    /**
     * Только подкатегории
     */
    public function scopeSubCategories(Builder $query): void
    {
        $query->where('parent_id', '!=', 0);
    }

    protected function parentSlug(): Attribute
    {
        if ($this->parent_id != 0) {
            return new Attribute(
                get: fn () => $this->parentCategory?->slug,
            );
        }

        return new Attribute(
            get: fn () => null,
        );
    }

    public function isMain(): bool
    {
        return $this->parent_id == 0;
    }

    public function isSub(): bool
    {
        return $this->parent_id != 0;
    }

    public function isPromo(): bool
    {
        return $this->is_promo;
    }
}
