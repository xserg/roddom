<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\Lector;
use App\Models\Lecture;
use App\Traits\MoneyConversion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class CategoryRepository
{
    use MoneyConversion;

    public function __construct()
    {
    }

    public function getCategoryById(int $id): ?Category
    {
        return Category::find($id);
    }

    /**
     * Считать цену учитывая только общую цену на лекции и количество лекций в категории
     */
    public function getCategoryPrice(int $categoryId, int $periodId): int|string
    {
        $category = $this->getCategoryById($categoryId);

        if (! is_null($category)) {
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

    public function formCategoryPrices(Category $category): array
    {
        $prices = $category->categoryPrices()->with(['period'])->get();
        $id = $category->id;
        $result = [];

        foreach ($prices as $price) {
            $priceForPackInRoubles = $this
                ->getCategoryPriceForPeriodComplex(
                    $id,
                    $price->period->id
                );

            $priceForOneLectureInRoubles = self::coinsToRoubles($price->price_for_one_lecture);

            $result[] = [
                'title' => $price->period->title,
                'length' => $price->period->length,
                'price_for_one_lecture' => (float) $priceForOneLectureInRoubles,
                'price_for_category' => $priceForPackInRoubles,
            ];
        }

        return $result;
    }

    public function getCategoryPriceForPeriodComplex(
        int $categoryId,
        int $periodId
    ): int|string|float {
        $finalPrice = 0;

        $lectures = Lecture::query()
            ->where('category_id', $categoryId)
            ->with([
                'category.categoryPrices',
                'contentType',
                'paymentType',
                'pricesPeriodsInPromoPacks',
                'pricesForLectures',
                'pricesInPromoPacks',
            ])
            ->get();

        $lecturesCount = $lectures->count();

        if ($lecturesCount == 0) {
            return $finalPrice;
        }

        foreach ($lectures as $lecture) {
            $lecturePrices = $lecture->prices;

            if (! $lecturePrices) {
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

        return round($finalPrice, 2);
    }

    /**
     * @deprecated
     */
    public function getCategoryPriceForPeriodLength(?Category $category, int $period): string|int|float
    {
        $prices = $category->prices;

        if ($prices) {
            $priceForExactPeriod = Arr::where(
                $prices,
                fn ($value) => $value['length'] == $period
            );
            $price = Arr::first($priceForExactPeriod)['price_for_category'];
        }

        return $price;
    }

    /**
     * Возвращает лекторов лекций, относящихся к категории/подкатегории
     * @param string $slug
     * @return Collection
     */
    public function getAllLectorsByCategory(string $slug): Collection
    {
        $category = Category::query()
            ->where('slug', '=', $slug)
            ->firstOrFail();

        if ($category->isMain()) {
            $subCategoriesIds = Category::subCategories()
                ->where('parent_id', '=', $category->id)
                ->pluck('id');

            if ($subCategoriesIds->isEmpty()) {
                return collect();
            }

            return Lector::whereHas(
                'lectures',
                function (Builder $query) use ($subCategoriesIds) {
                    $query->whereIn('category_id', $subCategoriesIds);
                })
                ->orderBy('id')
                ->get();
        }

        return Lector::whereHas(
            'lectures',
            function (Builder $query) use ($category) {
                $query->where('category_id', $category->id);
            })
            ->orderBy('id')
            ->get();
    }
}
