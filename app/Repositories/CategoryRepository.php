<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Support\Arr;

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
}
