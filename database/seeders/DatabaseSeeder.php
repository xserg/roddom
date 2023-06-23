<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Diploma;
use App\Models\Lector;
use App\Models\Lecture;
use App\Models\Period;
use App\Models\Promo;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\NewAccessToken;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    private array $previewPictures = [
        'images/users/user1.jpg',
        'images/users/user2.jpg',
        'images/users/user3.jpg',
    ];

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
//        Lector::factory(25)->create();
//        Diploma::factory(50)->create();
//        $this->call(SubscriptionPeriodSeeder::class);
//        $this->call(CategorySeeder::class);
//        $this->call(SubCategorySeeder::class);
//        $this->call(LectureTypeSeeder::class);
//        $this->call(PromoSeeder::class);
//        Lecture::factory(1050)->create();
//        Lecture::factory(30)->notPublished()->create();
//
//        $this->call(PromoPackPricesSeeder::class);
//        $this->call(CategoryPricesSeeder::class);
////        $this->call(PromoLecturesSeeder::class);
//        $this->call(PromoLecturesPricesSeeder::class);
//        $this->call(AppInfoSeeder::class);
//
//        $this->createFirstTestUser();
//        $this->createSecondTestUser();
//        $this->createAnotherUser();
//        $this->createUsers(1000);
//        $this->setRecommendedLectures(20);
        User::query()->update([
            'ref_token' => Str::uuid(4)
        ]);
    }

    private function createUsers(int $users)
    {
        User::factory($users)
            ->create()
            ->each(function ($user) {
                /**
                 * @var User $user
                 */
                $this->setDiffLectureTypes($user);
                $this->createSubscriptionsForUser($user);
            });
    }

    private function createSubscriptionsForUser($user)
    {
        $periodDay = Period::query()->firstWhere('title', '=', 'day');
        $periodWeek = Period::query()->firstWhere('title', '=', 'week');
        $periodMonth = Period::query()->firstWhere('title', '=', 'month');

        $randomSubcategory = Category::subCategories()->get()->random();

        $randomLectures = Lecture::where('category_id', '!=', $randomSubcategory->id)
            ->get()
            ->random(10);

        foreach ($randomLectures as $lecture) {
            $randomPeriod = fake()->randomElement([$periodDay, $periodWeek, $periodMonth]);
            $attributes = [
                'user_id' => $user->id,
                'subscriptionable_type' => Lecture::class,
                'subscriptionable_id' => $lecture->id,
                'period_id' => $randomPeriod->id,
                'start_date' => now(),
                'end_date' => now()->addHours($randomPeriod->length),
            ];

            $subscription = new Subscription($attributes);
            $subscription->save();
        }

        $attributes = [
            'user_id' => $user->id,
            'subscriptionable_type' => Category::class,
            'subscriptionable_id' => $randomSubcategory->id,
            'period_id' => $randomPeriod->id,
            'start_date' => now(),
            'end_date' => now()->addHours($randomPeriod->length),
        ];

        $subscription = new Subscription($attributes);
        $subscription->save();

        $attributes = [
            'user_id' => $user->id,
            'subscriptionable_type' => Promo::class,
            'subscriptionable_id' => Promo::first()->id,
            'period_id' => $randomPeriod->id,
            'start_date' => now(),
            'end_date' => now()->addHours($randomPeriod->length),
        ];

        $subscription = new Subscription($attributes);
        $subscription->save();
    }

    private function createFirstTestUser()
    {
        $photo = fake()->randomElement($this->previewPictures);
        $user = [
            'name' => 'test',
            'email' => 'test@test.test',
            'password' => Hash::make('test'),
            'photo' => $photo,
            'photo_small' => $photo,
            'birthdate' => Carbon::today()->subYears(rand(20, 35)),
            'phone' => fake()->phoneNumber,
            'is_mother' => rand(0, 1),
            'remember_token' => Str::random(10),
        ];

        DB::table('users')->insert($user);

        $user = User::first();

        $token = $user->tokens()->create([
            'name' => 'auth_token',
            'token' => hash('sha256', env('TEST_USER1_PLAIN')),
            'abilities' => '[*]',
            'expires_at' => null,
        ]);

        $tokenPlain = new NewAccessToken($token, $user->id.'|'.env('TEST_USER1_PLAIN'));

        $this->setDiffLectureTypes($user);
        $this->createSubscriptionsForUser($user);
    }

    private function createSecondTestUser()
    {
        $photo = fake()->randomElement($this->previewPictures);
        $user = [
            'name' => 'foo',
            'email' => 'foo@foo.foo',
            'password' => Hash::make('foo'),
            'photo' => $photo,
            'photo_small' => $photo,
            'birthdate' => Carbon::today()->subYears(rand(20, 35)),
            'phone' => fake()->phoneNumber,
            'is_mother' => rand(0, 1),
            'remember_token' => Str::random(10),
            'is_admin' => false,
        ];

        DB::table('users')->insert($user);

        $user = User::firstWhere('email', 'foo@foo.foo');

        $token = $user->tokens()->create([
            'name' => 'auth_token',
            'token' => hash('sha256', env('TEST_USER2_PLAIN')),
            'abilities' => '[*]',
            'expires_at' => null,
        ]);

        $tokenPlain = new NewAccessToken($token, $token->getKey().'|'.env('TEST_USER2_PLAIN'));

        $this->setDiffLectureTypes($user);
        $this->createSubscriptionsForUser($user);
    }

    private function createAnotherUser()
    {
        $user = [
            'name' => 'admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make(env('ADMIN_USER_PWD')),
            'remember_token' => Str::random(10),
            'is_admin' => true,
        ];

        DB::table('users')->insert($user);
    }

    private function setRecommendedLectures(int $num)
    {
        Lecture::all()->random($num)->each(function ($lecture) {
            $lecture->setRecommended();
            $lecture->save();
        });
    }

    private function setDiffLectureTypes($user)
    {
        $lectures = Lecture::all()
            ->random(150);

        foreach ($lectures as $lecture) {
            $rand = rand(0, 2);
            match ($rand) {
                0 => $user->watchedLectures()->attach($lecture->id),
                1 => $user->savedLectures()->attach($lecture->id),
                2 => $user->listWatchedLectures()->attach($lecture->id)
            };
        }
    }
}
