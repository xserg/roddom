<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Diplom;
use App\Models\Lector;
use App\Models\Lecture;
use App\Models\User;
use Database\Factories\DiplomFactory;
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
        Lecture::factory(50)->create();
        Diplom::factory(30)->create();
        $this->call(CategorySeeder::class);
        $this->call(SubCategorySeeder::class);
    }
}
