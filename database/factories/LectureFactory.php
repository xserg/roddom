<?php

namespace Database\Factories;

use App\Models\Lector;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lection>
 */
class LectureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'lector_id' => Lector::all()->random()->id,
            'title' => $this->faker->sentence(5),
            'category_id' => Category::query()
                ->where('parent_id', '!=', '0')
                ->get()
                ->random()
                ->id,
            'description' => $this->faker->text(100),
            'preview_picture' => $this->faker->imageUrl,
            'video_id' => $this->faker->randomNumber(9, true),
            'is_free' => rand(0, 1)
        ];
    }
}
