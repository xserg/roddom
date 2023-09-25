<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\Lector;
use App\Models\Lecture;
use App\Traits\MoneyConversion;
use Illuminate\Database\Eloquent\Builder;
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

    /**
     * Возвращает лекторов лекций, относящихся к категории/подкатегории
     */
    public function getAllLectorsByCategory(string $slug): Collection
    {
        $relationships = ['userRate', 'averageRate'];
        $category = Category::query()
            ->where('slug', $slug)
            ->firstOrFail();

        if ($category->isMain()) {
            $subCategoriesIds = Category::subCategories()
                ->where('parent_id', $category->id)
                ->pluck('id');

            if ($subCategoriesIds->isEmpty()) {
                return collect();
            }

            return Lector::whereHas(
                'lectures',
                function (Builder $query) use ($subCategoriesIds) {
                    $query->whereIn('category_id', $subCategoriesIds);
                })
                ->with($relationships)
                ->orderBy('id')
                ->get();
        }

        return Lector::whereHas(
            'lectures',
            function (Builder $query) use ($category) {
                $query->where('category_id', $category->id);
            })
            ->with($relationships)
            ->orderBy('id')
            ->get();
    }

    public function getAllLecturesByCategory(int $id, array $relationships = []): Collection
    {
        $category = Category::findOrFail($id);

        if ($category->isMain()) {
            $subCategoriesIds = Category::subCategories()
                ->where('parent_id', $category->id)
                ->pluck('id');

            if ($subCategoriesIds->isEmpty()) {
                return collect();
            }

            return Lecture::whereIn('category_id', $subCategoriesIds)
                ->with($relationships)
                ->orderBy('id')
                ->get();
        }

        return Lecture::where('category_id', $category->id)
            ->with($relationships)
            ->orderBy('id')
            ->get();
    }

    public function getCategoryBySlug(string $slug, array $with = [], array $withCount = []): Category
    {
        $withMain = [
            'childrenCategories' => fn ($query) => $query->withCount('lectures'),
            'childrenCategories.categoryPrices.period',
            'childrenCategories.parentCategory',
            'childrenCategories.lectures.category.categoryPrices',
            'childrenCategories.lectures.category.parentCategory.categoryPrices',
            'childrenCategories.lectures.pricesInPromoPacks',
            'childrenCategories.lectures.pricesForLectures',
            'childrenCategories.lectures.pricesPeriodsInPromoPacks',
            'childrenCategories.lectures.paymentType',
            'childrenCategories.lectures.contentType',
        ];
        $withSub = [
            'categoryPrices.period',
            'categoryPrices',
            'parentCategory.categoryPrices',
            'lectures.category.categoryPrices',
            'lectures.pricesInPromoPacks',
            'lectures.pricesForLectures',
            'lectures.pricesPeriodsInPromoPacks',
            'lectures.paymentType',
            'lectures.contentType',
        ];
        $withCountMain = ['childrenCategoriesLectures'];
        $withCountSub = ['lectures'];

        $category = Category::query()
            ->where('slug', $slug)
            ->first();

        return $category->isMain()
            ? $category->load($withMain)->loadCount($withCountMain)
            : $category->load($withSub)->loadCount($withCountSub);
    }
}
