<?php

namespace Database\Seeders;

use App\Models\Period;
use App\Models\Promo;
use Illuminate\Database\Seeder;

class PromoPackPricesSeeder extends Seeder
{
    public function run(): void
    {
        $periods = Period::all();
        $promo = Promo::first();

        foreach ($periods as $period) {
            $promo->subscriptionPeriodsForPromoPack()->attach($period->id, [
                'promo_id' => $promo->id,
                'price' => $period->id * mt_rand(100000, 110000),
                'price_for_one_lecture' => 9999,
            ]);
        }

        $promo->save();
    }
}
