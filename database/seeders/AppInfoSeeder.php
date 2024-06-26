<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AppInfoSeeder extends Seeder
{
    public function run(): void
    {
        $aboutApp = '(Описание приложения идёт сюда) Идейные соображения высшего порядка, а также постоянное информационно-пропагандистское обеспечение нашей деятельности позволяет оценить значение модели развития. Значимость этих проблем настолько очевидна, что реализация намеченных плановых заданий способствует подготовки и реализации соответствующий условий активизации.';
        $agreementText = 'Заголовок
Подзаголовок
Идейные соображения высшего порядка, а также постоянное информационно-пропагандистское обеспечение нашей деятельности позволяет оценить значение модели развития. Значимость этих проблем настолько очевидна, что реализация намеченных плановых заданий способствует подготовки и реализации соответствующий условий активизации.
Подзаголовок
Идейные соображения высшего порядка, а также постоянное информационно-пропагандистское обеспечение нашей деятельности позволяет оценить значение модели развития. Значимость этих проблем настолько очевидна, что реализация намеченных плановых заданий способствует подготовки и реализации соответствующий условий активизации.
Подзаголовок
Идейные соображения высшего порядка, а также постоянное информационно-пропагандистское обеспечение нашей деятельности позволяет оценить значение модели развития. Значимость этих проблем настолько очевидна, что реализация намеченных плановых заданий способствует подготовки и реализации соответствующий условий активизации.
Подзаголовок
Идейные соображения высшего порядка, а также постоянное информационно-пропагандистское обеспечение нашей деятельности позволяет оценить значение модели развития. Значимость этих проблем настолько очевидна, что реализация намеченных плановых заданий способствует подготовки и реализации соответствующий условий активизации.
Подзаголовок
Идейные соображения высшего порядка, а также постоянное информационно-пропагандистское обеспечение нашей деятельности позволяет оценить значение модели развития. Значимость этих проблем настолько очевидна, что реализация намеченных плановых заданий способствует подготовки и реализации соответствующий условий активизации.
Принять и продолжить';
        DB::insert('INSERT INTO app_info (agreement_text, about_app)
                           VALUES (?,?)', [$agreementText, $aboutApp]);

        $title1 = 'Как делать то?';
        $title2 = 'Что значит это?';
        $title3 = 'А что если вот так?';
        $text1 = 'Идейные соображения высшего порядка, а также постоянное информационно-пропагандистское обеспечение нашей деятельности позволяет оценить значение модели развития. Значимость этих проблем настолько очевидна, что реализация намеченных плановых заданий способствует подготовки и реализации соответствующий условий активизации.';
        $text2 = 'Идейные соображения высшего порядка, а также постоянное информационно-пропагандистское обеспечение нашей деятельности позволяет оценить значение модели развития. Значимость этих проблем настолько очевидна, что реализация намеченных плановых заданий способствует подготовки и реализации соответствующий условий активизации.';
        $text3 = 'Идейные соображения высшего порядка, а также постоянное информационно-пропагандистское обеспечение нашей деятельности позволяет оценить значение модели развития. Значимость этих проблем настолько очевидна, что реализация намеченных плановых заданий способствует подготовки и реализации соответствующий условий активизации.';

        DB::insert('INSERT INTO app_help_page (title, text)
                           VALUES (?, ?)', [$title1, $text1]);

        DB::insert('INSERT INTO app_help_page (title, text)
                           VALUES (?, ?)', [$title2, $text2]);

        DB::insert('INSERT INTO app_help_page (title, text)
                           VALUES (?, ?)', [$title3, $text3]);
    }
}
