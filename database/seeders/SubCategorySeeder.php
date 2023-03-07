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
            $parentCategory = Category::all()
                ->where('parent_id', '=', 0)
                ->random();
            $category = [
                'parent_id' => $parentCategory->id,
                'parent_slug' => Str::slug($parentCategory->title),
                'preview_picture' => fake()->imageUrl,
                'title' => 'Название подкатегории - ' . $i,
                'slug' => Str::slug('Название подкатегории - ' . $i),
                'description' => fake()->text(150),
                'info' => fake()->text(),
            ];
            (new Category($category))->save();
        }
    }
}
