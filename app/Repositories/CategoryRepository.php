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

    /**
     * Возвращает лекторов лекций, относящихся к категории/подкатегории
     */
    public function getAllLectorsByCategory(string $slug): Collection
    {
        $relationships = ['userRate', 'averageRate'];
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
}
