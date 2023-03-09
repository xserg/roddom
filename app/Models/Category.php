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

    protected $appends = ['prices'];

    protected $table = 'lecture_categories';

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

        foreach ($prices as $price){
            $priceForPackInRoubles = number_format($price->price_for_pack / 100, 2);
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
}
