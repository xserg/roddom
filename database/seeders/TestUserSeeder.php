<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TestUserSeeder extends Seeder
{
    public function __construct(\Faker\Generator $faker)
    {
        $this->faker = $faker;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = [
            'name' => 'test',
            'email' => 'test@test.test',
            'password' => 'test',
            'birthdate' => Carbon::today()->subYears(rand(20, 35)),
            'phone' => $this->faker->phoneNumber,
            'mother' => rand(0, 1),
            'remember_token' => Str::random(10),
        ];

        DB::table('users')->insert($user);
    }
}
