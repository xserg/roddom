<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubscriptionPeriodSeeder extends Seeder
{
    public function run(): void
    {
        $subscriptions = [
            [
                'title' => 'day',
                'length' => 1,
            ],
            [
                'title' => 'week',
                'length' => 14,
            ],
            [
                'title' => 'month',
                'length' => 30,
            ],
        ];

        DB::table('subscription_periods')->insert($subscriptions);
    }
}
