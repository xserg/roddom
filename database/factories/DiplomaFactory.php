<?php

namespace Database\Factories;

use App\Models\Lector;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class DiplomaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $previewPictures = [
            'images/diplomas/diploma1.jpg',
            'images/diplomas/diploma2.jpg',
            'images/diplomas/diploma3.jpg',
        ];
        return [
            'preview_picture' => fake()->randomElement($previewPictures),
            'lector_id' => Lector::all()->random()
        ];
    }
}
