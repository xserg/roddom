<?php

namespace Database\Seeders;

use App\Models\Lector;
use App\Models\User;
use Illuminate\Database\Seeder;

class LectorRatesSeeder extends Seeder
{
    public function run(): void
    {
        Lector::all()->each(function (Lector $lector) {
            $users = User::inRandomOrder()->take(950);

            $users->each(function (User $user) use ($lector) {
                $lector->rates()->create([
                    'user_id' => $user->id,
                    'rating' => rand(1, 10)
                ]);
            });

        });
    }
}
