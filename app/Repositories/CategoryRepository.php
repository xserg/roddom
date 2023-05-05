<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\Lecture;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class CategoryRepository
{
    public function __construct()
    {
    }

    public function getCategoryById(int $id): ?Category
    {
        return Category::find($id);
    }

    /**
     * Считать цену учитывая только общую цену на лекции и количество лекций в категории
     * @param int $categoryId
     * @param int $periodId
     * @return int|string
     */
    public function getCategoryPrice(int $categoryId, int $periodId): int|string
    {
        $category = $this->getCategoryById($categoryId);

        if (!is_null($category)) {
            return 0;
        }

        $prices = $category->categoryPrices;
        $lecturesCount = $category->lectures->count();

        foreach ($prices as $price) {
            $priceForPackInRoubles =
                number_format(($price->price_for_one_lecture * $lecturesCount) / 100, 2, thousands_separator: '');

            if ($price->period->id == $periodId) {
                return $priceForPackInRoubles;
            }
        }
        return 0;
    }

    public function getCategoryPriceForPeriodComplex(
        int $categoryId,
        int $periodId
    ): int|string|float
    {
        $finalPrice = 0;

        $lectures = Lecture::query()->where('category_id', $categoryId)->get();
        $lecturesCount = $lectures->count();

        if ($lecturesCount == 0) {
            return $finalPrice;
        }

        foreach ($lectures as $lecture) {
            $lecturePrices = $lecture->prices;

            if (!$lecturePrices) {
                continue;
            }

            $lecturePriceForPeriod = Arr::where($lecturePrices, function ($value) use ($periodId) {
                return $value['period_id'] == $periodId;
            });

            $lecturePriceForPeriod = Arr::first($lecturePriceForPeriod);

            $customPrice = $lecturePriceForPeriod['custom_price_for_one_lecture'];
            $commonPrice = $lecturePriceForPeriod['common_price_for_one_lecture'];

            $finalPrice += $customPrice ?? $commonPrice;
        }

        return $finalPrice;
    }

    /**
     * @deprecated
     * @param Category|null $category
     * @param int $period
     * @return string|int|float
     */
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
