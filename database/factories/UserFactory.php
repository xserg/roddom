<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $previewPictures = [
            'images/users/user1.jpg',
            'images/users/user2.jpg',
            'images/users/user3.jpg',
        ];

        $randomPhoto = fake()->randomElement($previewPictures);

        return [
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'name' => fake()->firstNameFemale,
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'birthdate' => Carbon::today()->subYears(rand(20, 35)),
            'is_mother' => rand(0, 1),
            'pregnancy_start' => Carbon::today()->subMonths(rand(3, 12)),
            'baby_born' => Carbon::today()->subMonths(rand(1, 2)),
            'phone' => fake()->phoneNumber,
            'photo' => $randomPhoto,
            'photo_small' => $randomPhoto
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified()
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
