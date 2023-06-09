<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\Lector;
use App\Models\Lecture;
use App\Models\Period;
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

    public function getCategoryById(int $id, array $relations = []): ?Category
    {
        return Category::with($relations)->find($id);
    }

    public function formMainCategoryPrices(Category $category): array
    {
        $result = [];

        foreach (Period::all() as $period) {
            $categoryPrice = $this->calculateMainCategoryPriceForPeriod($category, $period->id);

            $categoryPriceInRoubles = self::coinsToRoubles($categoryPrice);

            $result[] = [
                'title' => $period->title,
                'length' => $period->length,
                'price_for_category' => $categoryPriceInRoubles,
            ];
        }

        return $result;
    }

    public function formSubCategoryPrices(Category $category): array
    {
        $prices = $category->categoryPrices;
        $result = [];

        foreach ($prices as $price) {
            $categoryPrice = $this->calculateSubCategoryPriceForPeriod($category, $price->period->id);

            $priceForOneLectureInRoubles = self::coinsToRoubles($price->price_for_one_lecture);
            $categoryPriceInRoubles = self::coinsToRoubles($categoryPrice);

            $result[] = [
                'title' => $price->period->title,
                'length' => $price->period->length,
                'price_for_one_lecture' => $priceForOneLectureInRoubles,
                'price_for_category' => $categoryPriceInRoubles,
            ];
        }

        return $result;
    }

    public function calculateMainCategoryPriceForPeriod(
        Category $mainCategory,
        int $periodId
    ): int {
        $price = 0;

        foreach ($mainCategory->childrenCategories as $subCategory) {
            $price += $this->calculateSubCategoryPriceForPeriod($subCategory, $periodId);
        }

        return $price;
    }

    /**
     * Считает цену сабкатегории в копейках за период
     */
    public function calculateSubCategoryPriceForPeriod(
        Category $category,
        int $periodId
    ): int {
        $finalPrice = 0;

        $lecturesCount = $category->lectures->count();

        if ($lecturesCount === 0) {
            return $finalPrice;
        }

        foreach ($category->lectures as $lecture) {

            // !аксессор лекции
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

        return $finalPrice;
    }

    /**
     * Возвращает лекторов лекций, относящихся к категории/подкатегории
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
}
