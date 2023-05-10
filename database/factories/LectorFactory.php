<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lector>
 */
class LectorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $previewPictures = [
            'images/lectors/lector1.jpg',
            'images/lectors/lector2.jpg',
            'images/lectors/lector3.jpg',
        ];

        return [
            'name' => $this->faker->name,
            'position' => $this->faker->jobTitle,
            'description' => $this->faker->text(200),
            'career_start' => Carbon::today()->subYears(rand(2, 25)),
            'photo' => $this->faker->randomElement($previewPictures),
        ];
    }
}
