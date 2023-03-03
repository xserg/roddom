<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SubcategoryPrices;
use Illuminate\Database\Seeder;

class CategoryPricesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::subCategories()->get();

        foreach ($categories as $category) {
            $categoryPrices1 = new SubcategoryPrices(
                [
                    'category_id' => $category->id,
                    'price_for_pack' => mt_rand(100000, 120000),
                    'price_for_one_lecture' => mt_rand(10000, 12000),
                    'period_id' => 1
                ]
            );
            $categoryPrices1->save();

            $categoryPrices1 = new SubcategoryPrices(
                [
                    'category_id' => $category->id,
                    'price_for_pack' => mt_rand(300000, 320000),
                    'price_for_one_lecture' => mt_rand(30000, 32000),
                    'period_id' => 2
                ]
            );
            $categoryPrices1->save();

            $categoryPrices1 = new SubcategoryPrices(
                [
                    'category_id' => $category->id,
                    'price_for_pack' => mt_rand(500000, 520000),
                    'price_for_one_lecture' => mt_rand(50000, 52000),
                    'period_id' => 3
                ]
            );
            $categoryPrices1->save();
        }
    }
}
