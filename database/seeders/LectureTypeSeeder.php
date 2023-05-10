<?php

namespace Database\Seeders;

use App\Models\LectureContentType;
use App\Models\LecturePaymentType;
use Illuminate\Database\Seeder;

class LectureTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['title' => 'kinescope', 'title_ru' => 'Кинескоп'],
            ['title' => 'pdf', 'title_ru' => 'PDF'],
            ['title' => 'embed', 'title_ru' => 'youtube/rutube'],
        ];

        foreach ($types as $type) {
            (new LectureContentType($type))->save();
        }

        $paymentTypes = [
            ['title' => 'free', 'title_ru' => 'Бесплатная'],
            ['title' => 'pay', 'title_ru' => 'Платная'],
            ['title' => 'akcia', 'title_ru' => 'Акционная'],
        ];

        foreach ($paymentTypes as $type) {
            (new LecturePaymentType($type))->save();
        }
    }
}
