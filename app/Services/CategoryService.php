<?php

namespace App\Services;

use App\Models\Category;

class CategoryService
{
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
}
