<?php

namespace Database\Seeders;

use App\Models\Lecture;
use App\Models\Promo;
use Illuminate\Database\Seeder;

class PromoLecturesSeeder extends Seeder
{
    public function run(): void
    {
        $lectures = Lecture::payed()
            ->get()
            ->random(20);
        $promo = Promo::first();

        foreach ($lectures as $lecture) {
            $promo->promoLectures()->attach($lecture->id);
        }
    }
}
