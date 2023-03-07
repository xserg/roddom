<?php

namespace Database\Seeders;

use App\Models\Lecture;
use App\Models\Period;
use App\Models\Promo;
use Illuminate\Database\Seeder;

class PromoLecturesPricesSeeder extends Seeder
{
    public function run(): void
    {
        $periods = Period::all();
        $promo = Promo::first();

        foreach ($promo->promoLectures as $lecture){
            foreach ($periods as $period){
                $lecture->pricesInPromoPacks()->attach($promo->id, [
                    'lecture_id' => $lecture->id,
                    'period_id' => $period->id,
                    'price' => $period->id * mt_rand(10000, 11000)
                ]);
                $lecture->save();
            }
        }
    }
}
