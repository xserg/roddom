<?php

namespace Database\Factories;

use App\Models\Lector;
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
            'description' => $this->faker->text(100),
            'preview_picture' => $this->faker->imageUrl,
            'video' => $this->faker->url
        ];
    }
}
