<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\CategoryRepository;

class CategoryService
{
    public function __construct(
        private CategoryRepository $categoryRepository
    )
    {
    }

    public function isCategoryMain(Category $category): bool
    {
        return $category->parent_id == 0;
    }

    public function isCategorySub(Category $category): bool
    {
        return $category->parent_id != 0;
    }

    public function isCategoryPurchased($id)
    {
        $purchasedCategoriesIds = auth()->user()
            ->categorySubscriptions()
            ->get()
            ->pluck('subscriptionable_id');

        return $purchasedCategoriesIds->contains($id);
    }

    public function getCategoryPrice(int $categoryId, int $periodLength): int|string
    {
        $category = $this->categoryRepository->getCategoryById($categoryId);
        if (!$category) {
            return 0;
        }

        $prices = $category->categoryPrices;
        $lecturesCount = $category->lectures->count();

        foreach ($prices as $price) {
            $priceForPackInRoubles =
                number_format(($price->price_for_one_lecture * $lecturesCount) / 100, 2, thousands_separator: '');

            if($price->period->id == $periodLength){
                return $priceForPackInRoubles;
            }
        }
        return 0;
    }
}
