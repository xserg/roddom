<?php

namespace Database\Seeders;

use App\Models\Period;
use App\Models\Promo;
use Illuminate\Database\Seeder;

class PromoSeeder extends Seeder
{
    public function run(): void
    {
        $promo = new Promo();
        $promo->save();
    }
}
