<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Category extends Model
{
    use HasFactory;

    protected $appends = ['prices', 'parent_slug'];

    protected $fillable = ['parent_id', 'title', 'description', 'info', 'slug',
        'preview_picture'];
    protected $table = 'lecture_categories';

    protected static function booted(): void
    {
        static::created(function (Category $category) {
            if ($category->categoryPrices->isNotEmpty()
                || $category->parent_id == 0) {
                return;
            }

            $categoryPricesDay = new SubcategoryPrices(
                [
                    'category_id' => $category->id,
                    'price_for_pack' => null,
                    'price_for_one_lecture' => 10000,
                    'period_id' => 1
                ]
            );
            $categoryPricesDay->save();

            $categoryPricesWeek = new SubcategoryPrices(
                [
                    'category_id' => $category->id,
                    'price_for_pack' => null,
                    'price_for_one_lecture' => 20000,
                    'period_id' => 2
                ]
            );
            $categoryPricesWeek->save();

            $categoryPricesMonth = new SubcategoryPrices(
                [
                    'category_id' => $category->id,
                    'price_for_pack' => null,
                    'price_for_one_lecture' => 50000,
                    'period_id' => 3
                ]
            );
            $categoryPricesMonth->save();
        });
    }

    public function parentCategory(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function lectures(): HasMany
    {
        return $this->hasMany(Lecture::class);
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

    protected function prices(): Attribute
    {
        $prices = $this->categoryPrices;
        $result = [];

        $lecturesCount = $this->lectures()->count();
        foreach ($prices as $price) {
            $priceForPackInRoubles =
                number_format(($price->price_for_one_lecture * $lecturesCount) / 100, 2, thousands_separator: '');

            $result[] = [
                'title' => $price->period->title,
                'length' => $price->period->length,
                'price_for_category' => $priceForPackInRoubles
            ];
        }

        if ($result) {
            return new Attribute(
                get: fn() => $result,
            );
        }
        return new Attribute(
            get: fn() => [],
        );
    }

    protected function parentSlug(): Attribute
    {
        if ($this->parent_id != 0) {
            return new Attribute(
                get: fn() => $this->parentCategory->slug,
            );
        }

        return new Attribute(
            get: fn() => null,
        );
    }
}
