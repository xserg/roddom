<?php

namespace App\Http\Controllers\Api\AppInfo;

use App\Http\Controllers\Controller;
use App\Models\AppInfo;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Get(
    path: '/app/agreement',
    summary: 'Получение лицензионного соглашения приложения',
    tags: ['app'])
]
#[OA\Response(response: Response::HTTP_OK, description: 'OK',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data', example: [[
            "agreement_title" => "Прочтите лицензионное соглашение тут",
            "agreement_text" => "Текст соглашения",
        ]]),
    ]))]
#[OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: 'Server Error')]
class AppAgreementController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $appInfo = AppInfo::select(['agreement_title', 'agreement_text'])->first();

        return response()->json([
            'data' => [
                $appInfo
            ],
        ]);
    }
}
