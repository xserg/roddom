<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Lecture;
use App\Models\Period;
use App\Models\Promo;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $periodDay = Period::query()->firstWhere('title', '=', 'day');
        $periodWeek = Period::query()->firstWhere('title', '=', 'week');
        $periodMonth = Period::query()->firstWhere('title', '=', 'month');

        $randomSubcategory = Category::subCategories()->get()->random();

        $randomLectures = Lecture::where('category_id', '!=', $randomSubcategory->id)
            ->get()
            ->random(10);

        foreach ($randomLectures as $lecture) {
            $randomPeriod = fake()->randomElement([$periodDay, $periodWeek, $periodMonth]);
            $attributes = [
                'user_id' => User::first()->id,
                'subscriptionable_type' => Lecture::class,
                'subscriptionable_id' => $lecture->id,
                'period_id' => $randomPeriod->id,
                'start_date' => now(),
                'end_date' => now()->addHours($randomPeriod->length),
            ];

            $subscription = new Subscription($attributes);
            $subscription->save();
        }

        $attributes = [
            'user_id' => User::first()->id,
            'subscriptionable_type' => Category::class,
            'subscriptionable_id' => $randomSubcategory->id,
            'period_id' => $randomPeriod->id,
            'start_date' => now(),
            'end_date' => now()->addHours($randomPeriod->length),
        ];

        $subscription = new Subscription($attributes);
        $subscription->save();

        $attributes = [
            'user_id' => User::first()->id,
            'subscriptionable_type' => Promo::class,
            'subscriptionable_id' => Promo::first()->id,
            'period_id' => $randomPeriod->id,
            'start_date' => now(),
            'end_date' => now()->addHours($randomPeriod->length),
        ];

        $subscription = new Subscription($attributes);
        $subscription->save();
    }
}
