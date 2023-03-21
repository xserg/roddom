<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            'Беременность',
            'Роды',
            'Грудное вскармливание',
            'Уход за новорожденым',
            'Гинекология',
            'Онкопрофилактика',
            'Психология',
        ];

        $previewPictures = [
            'images/categories/category1.jpg',
            'images/categories/category2.jpg',
            'images/categories/category3.jpg',
        ];

        for ($i = 0; $i < count($categories); $i++) {
            $category = [
                'parent_id' => 0,
                'title' => $categories[$i],
                'slug' => Str::slug($categories[$i]),
                'preview_picture' => fake()->randomElement($previewPictures),
                'description' => fake()->text(150),
                'info' => fake()->text(),
            ];
            (new Category($category))->save();
        }
    }
}
