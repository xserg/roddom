<?php

namespace Database\Factories;

use App\Models\Lector;
use App\Models\LectureCategory;
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
            'title' => $this->faker->title,
            'category_id' => LectureCategory::query()
                ->where('id', '!=', '0')
                ->get()
                ->random()
                ->id,
            'description' => $this->faker->text(100),
            'preview_picture' => $this->faker->imageUrl,
            'video' => $this->faker->url
        ];
    }
}
