<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run()
    {
        for ($i = 0; $i < 5; $i++) {
            $category = [
                'parent_id' => 0,
                'title' => fake()->word,
                'description' => fake()->text(150),
                'info' => fake()->text(),
            ];

            DB::table('lecture_categories')->insert($category);
        }
    }
}
