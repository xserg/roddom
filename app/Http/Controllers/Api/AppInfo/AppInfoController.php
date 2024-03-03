<?php

namespace App\Http\Controllers\Api\AppInfo;

use App\Http\Controllers\Controller;
use App\Models\Period;
use App\Models\RefInfo;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Get(
    path: '/app/info',
    description: 'Получение динамических данных приложения, которые указываются в админке.
    Поля: app_help_page, app_info, app_periods, ref_info.
    app_help_page - инфа о странице помощи, текст вопроса: текст ответа,
    ref_info - инфа уровни реф программы: процент начислений
    с суммы покупки в реальной валюте',
    summary: 'Получение данных приложения',
    tags: ['app'])
]
#[OA\Response(response: Response::HTTP_OK, description: 'OK',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data', example: [
            'app_help_page' => [
                [
                    'title' => 'Как делать то?',
                    'text' => 'Идейные соображения высшего порядка, а также постоянное информационно-пропагандистское обеспечение нашей деятельности позволяет оценить значение модели развития. Значимость этих проблем настолько очевидна, что реализация намеченных плановых заданий способствует подготовки и реализации соответствующий условий активизации.',
                ],
                [
                    'title' => 'Что значит это?',
                    'text' => 'Идейные соображения высшего порядка, а также постоянное информационно-пропагандистское обеспечение нашей деятельности позволяет оценить значение модели развития. Значимость этих проблем настолько очевидна, что реализация намеченных плановых заданий способствует подготовки и реализации соответствующий условий активизации.',
                ],
                [
                    'title' => 'А что если вот так?',
                    'text' => 'Идейные соображения высшего порядка, а также постоянное информационно-пропагандистское обеспечение нашей деятельности позволяет оценить значение модели развития. Значимость этих проблем настолько очевидна, что реализация намеченных плановых заданий способствует подготовки и реализации соответствующий условий активизации.',
                ],
            ],
            'app_info' => [
                'agreement_title' => 'Прочтите соглашение',
                'agreement_text' => "Заголовок\nПодзаголовок\nИдейные соображения высшего порядка, а также постоянное информационно-пропагандистское обеспечение нашей деятельности позволяет оценить значение модели развития. Значимость этих проблем настолько очевидна, что реализация намеченных плановых заданий способствует подготовки и реализации соответствующий условий активизации.\nПодзаголовок\nИдейные соображения высшего порядка, а также постоянное информационно-пропагандистское обеспечение нашей деятельности позволяет оценить значение модели развития. Значимость этих проблем настолько очевидна, что реализация намеченных плановых заданий способствует подготовки и реализации соответствующий условий активизации.\nПодзаголовок\nИдейные соображения высшего порядка, а также постоянное информационно-пропагандистское обеспечение нашей деятельности позволяет оценить значение модели развития. Значимость этих проблем настолько очевидна, что реализация намеченных плановых заданий способствует подготовки и реализации соответствующий условий активизации.\nПодзаголовок\nИдейные соображения высшего порядка, а также постоянное информационно-пропагандистское обеспечение нашей деятельности позволяет оценить значение модели развития. Значимость этих проблем настолько очевидна, что реализация намеченных плановых заданий способствует подготовки и реализации соответствующий условий активизации.\nПодзаголовок\nИдейные соображения высшего порядка, а также постоянное информационно-пропагандистское обеспечение нашей деятельности позволяет оценить значение модели развития. Значимость этих проблем настолько очевидна, что реализация намеченных плановых заданий способствует подготовки и реализации соответствующий условий активизации.\nПринять и продолжить",
                'recommended_title' => 'Рекомендуем',
                'recommended_subtitle' => 'Не пропустите новые лекции!',
                'lectures_catalog_title' => 'Каталог лекций',
                'lectures_catalog_subtitle' => 'Выберите тему, которая вас интересует',
                'out_lectors_title' => 'Наши лекторы',
                'not_viewed_yet_title' => 'Вы ещё не смотрели',
                'more_in_the_collection' => 'Ещё в подборке',
                'about_lector_title' => 'О лекторе',
                'diplomas_title' => 'Дипломы и сертификаты',
                'lectors_videos' => 'Видео от лектора',
                'app_title' => 'Школа мам и пап «Нежность»',
                'about_app' => '(Описание приложения идёт сюда) Идейные соображения высшего порядка, а также постоянное информационно-пропагандистское обеспечение нашей деятельности позволяет оценить значение модели развития. Значимость этих проблем настолько очевидна, что реализация намеченных плановых заданий способствует подготовки и реализации соответствующий условий активизации.',
                'app_author_name' => 'Сергей Тарасов',
                'app_link_share_title' => 'Поделиться ссылкой',
                'app_link_share_link' => 'https://xn--80axb4d.online',
                'app_show_qr_title' => 'Показать QR-код',
                'app_show_qr_link' => 'https://api.мамы.online/storage/images/app/qr.jpeg',
            ],
            'app_periods' => [
                1,
                14,
                30
            ],
            'ref_info' => [
                [
                    'depth_level' => 1,
                    'percent' => 0.25
                ],
                [
                    'depth_level' => 2,
                    'percent' => 0.5
                ],
                [
                    'depth_level' => 3,
                    'percent' => 0.75
                ],
                [
                    'depth_level' => 4,
                    'percent' => 1
                ]
            ]
        ]),
    ]))]
#[OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: 'Server Error')]
class AppInfoController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => [
                'app_help_page' => DB::table('app_help_page')
                    ->select('title', 'text')
                    ->get(),
                'app_info' => DB::table('app_info')
                    ->select('*')
                    ->get(),
                'app_periods' => Period::all()->pluck('length'),
                'ref_info' => RefInfo::all(['depth_level', 'percent'])
            ],
        ]);
    }
}
