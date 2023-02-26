<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubCategorySeeder extends Seeder
{
    public function run()
    {
        for ($i = 1; $i < 26; $i++) {
            $category = [
                'parent_id' => Category::all()
                    ->where('parent_id', '=', 0)
                    ->random()
                    ->id,
                'title' => 'Название подкатегории - ' . $i,
                'slug' => Str::slug('Название подкатегории - ' . $i),
                'description' => fake()->text(150),
                'info' => fake()->text(),
            ];

            DB::table('lecture_categories')->insert($category);
        }
    }
}
