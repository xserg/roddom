<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Lector;
use App\Models\LectureContentType;
use App\Models\LecturePaymentType;
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
        $previewPictures = [
            'images/lectures/lecture1.jpg',
            'images/lectures/lecture2.jpg',
            'images/lectures/lecture3.jpg',
        ];

        return [
            'lector_id' => Lector::all()->random()->id,
            'title' => $this->faker->sentence(5),
            'category_id' => Category::query()
                ->where('parent_id', '!=', '0')
                ->get()
                ->random()
                ->id,
            'description' => $this->faker->text(100),
            'preview_picture' => $this->faker->randomElement($previewPictures),
            'content' => $this->faker->randomNumber(9, true),
            'is_published' => true,
            'payment_type_id' => LecturePaymentType::query()->get('id')->random()->id,
            'content_type_id' => LectureContentType::query()->get('id')->random()->id,
        ];
    }

    public function notPublished(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'is_published' => false,
            ];
        });
    }
}
