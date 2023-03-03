<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Diploma;
use App\Models\Lector;
use App\Models\Lecture;
use App\Models\User;
use Carbon\Carbon;
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
        Lector::factory(25)->create();
        Diploma::factory(50)->create();
        $this->call(CategorySeeder::class);
        $this->call(SubCategorySeeder::class);
        Lecture::factory(150)->create();
        $this->call(TestUserSeeder::class);
        $this->createUsers(20);

        $this->call(SubscriptionPeriodSeeder::class);
        $this->call(PromoSeeder::class);
        $this->call(PromoPackPricesSeeder::class);
        $this->call(CategoryPricesSeeder::class);
        $this->call(PromoLecturesPricesSeeder::class);
        $this->call(SubscriptionSeeder::class);
    }

    private function createUsers(int $users)
    {
        User::factory($users)
            ->create()
            ->each(function ($user) {
                /**
                 * @var User $user
                 */

                $lectures = Lecture
                    ::all()
                    ->random(50);

                foreach ($lectures as $lecture) {
                    $rand = rand(0, 2);
                    switch ($rand) {
                        case 0:
                        {
                            $user->watchedLectures()
                                ->attach($lecture->id);
                        }
                        case 1:
                        {
                            $user->savedLectures()
                                ->attach($lecture->id);
                        }
                        case 2:
                        {
                            $user->purchasedLectures()
                                ->attach(
                                    $lecture->id,
                                    ['purchased_until' => Carbon::now()->addDays($rand)]
                                );
                        }
                        default;
                    }
                }
            });
    }
}
