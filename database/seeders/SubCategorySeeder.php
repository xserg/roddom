<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SubCategorySeeder extends Seeder
{
    public function run()
    {
        $previewPictures = [
            'images/categories/category1.jpg',
            'images/categories/category2.jpg',
            'images/categories/category3.jpg',
        ];

        for ($i = 1; $i < 26; $i++) {
            $parentCategory = Category::all()
                ->where('parent_id', '=', 0)
                ->random();
            $category = [
                'parent_id' => $parentCategory->id,
                'preview_picture' => fake()->randomElement($previewPictures),
                'title' => 'Название подкатегории - '.$i,
                'slug' => Str::slug('Название подкатегории - '.$i),
                'description' => fake()->text(150),
                'info' => fake()->text(),
            ];
            (new Category($category))->save();
        }
    }
}
