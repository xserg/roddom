<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SubcategoryPrices;
use Illuminate\Database\Seeder;

class CategoryPricesSeeder extends Seeder
{
    public function run(): void
    {
        $categoryIdsWhichAlreadyHasPrices = SubcategoryPrices::query()->pluck('category_id');
        $categories = Category::whereNotIn('id', $categoryIdsWhichAlreadyHasPrices)->get();

        foreach ($categories as $category) {
            $categoryPricesDay = new SubcategoryPrices(
                [
                    'category_id' => $category->id,
                    'price_for_pack' => null,
                    'price_for_one_lecture' => 10000,
                    'price_for_one_lecture_promo' => 9000,
                    'period_id' => 1,
                ]
            );
            $categoryPricesDay->save();

            $categoryPricesWeek = new SubcategoryPrices(
                [
                    'category_id' => $category->id,
                    'price_for_pack' => null,
                    'price_for_one_lecture' => 20000,
                    'price_for_one_lecture_promo' => 15000,
                    'period_id' => 2,
                ]
            );
            $categoryPricesWeek->save();

            $categoryPricesMonth = new SubcategoryPrices(
                [
                    'category_id' => $category->id,
                    'price_for_pack' => null,
                    'price_for_one_lecture' => 50000,
                    'price_for_one_lecture_promo' => 40000,
                    'period_id' => 3,
                ]
            );
            $categoryPricesMonth->save();
        }
    }
}
