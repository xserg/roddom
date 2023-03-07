<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Lecture;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\NewAccessToken;

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
            'password' => Hash::make('test'),
            'birthdate' => Carbon::today()->subYears(rand(20, 35)),
            'phone' => $this->faker->phoneNumber,
            'is_mother' => rand(0, 1),
            'remember_token' => Str::random(10),
        ];

        DB::table('users')->insert($user);

        $user = User::first();

        $token = $user->tokens()->create([
            'name' => 'auth_token',
            'token' => hash('sha256', $plainTextToken = 'S5UQcrN2vnXSUfc8KoNh5xgEeipB2gyobh5Ms7IO'),
            'abilities' => '[*]',
            'expires_at' => null,
        ]);

        $tokenPlain = new NewAccessToken($token, $user->id.'|'.$plainTextToken);

        $lectures = Lecture
            ::all()
            ->random(50);

        foreach ($lectures as $lecture) {
            $rand = rand(0, 1);
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
                default;
            }
        }
    }
}
