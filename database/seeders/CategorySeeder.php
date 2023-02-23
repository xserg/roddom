<?php

namespace Database\Seeders;

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

        for ($i = 0; $i < 6; $i++) {
            $category = [
                'parent_id' => 0,
                'title' => $categories[$i],
                'slug' => Str::slug($categories[$i]),
                'description' => fake()->text(150),
                'info' => fake()->text(),
            ];

            DB::table('lecture_categories')->insert($category);
        }
    }
}
