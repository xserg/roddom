<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Diploma;
use App\Models\Lector;
use App\Models\Lecture;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::factory(20)->create();
        $this->call(TestUserSeeder::class);
        Lector::factory(5)->create();
        Diploma::factory(30)->create();
        $this->call(CategorySeeder::class);
        $this->call(SubCategorySeeder::class);
        Lecture::factory(50)->create();
    }
}
