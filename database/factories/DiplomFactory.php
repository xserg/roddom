<?php

namespace Database\Factories;

use App\Models\Lector;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class DiplomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'picture' => fake()->imageUrl,
            'lector_id' => Lector::all()->random()
        ];
    }
}
