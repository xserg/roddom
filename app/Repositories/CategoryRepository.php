<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class CategoryRepository
{
    public function __construct(
        private PeriodRepository $periodRepository
    )
    {
    }

    public function getCategoryById(int $id): ?Category
    {
        return Category::find($id);
    }

    public function getCategoryPriceForPeriodLength(?Category $category, int $period): string|int|float
    {
        $prices = $category->prices;

        if ($prices) {
            $priceForExactPeriod = Arr::where(
                $prices,
                fn($value) => $value['length'] == $period
            );
            $price = Arr::first($priceForExactPeriod)['price_for_category'];
        }

        return $price;
    }

    public function getAllLectorsByCategory(string $slug): Collection|array
    {
        $category = Category
            ::query()
            ->where('slug', '=', $slug)
            ->firstOrFail();

        $lectors = [];
        if ($category->parent_id === 0) {
            $subCategories = Category
                ::subCategories()
                ->where('parent_id', '=', $category->id)
                ->with('lectures.lector')
                ->get();

            if ($subCategories->isNotEmpty()) {
                $subCategories->each(function ($subCategory) use (&$lectors) {
                    $lectures = $subCategory->lectures;
                    $lectures->each(function ($lecture) use (&$lectors) {
                        $lector = $lecture->lector;
                        $lectors[] = $lector;
                    });
                });
            }
        } else {
            $lectures = $category->lectures;
            $lectures->each(function ($lecture) use (&$lectors) {
                $lector = $lecture->lector;
                $lectors[] = $lector;
            });
        }

        return collect($lectors)
            ->unique()
            ->sort()
            ->values();
    }
}
