<?php

namespace App\Http\Controllers\Api\Wizard;

use App\Http\Controllers\Controller;
use App\Http\Resources\WizardResource;
use App\Models\Wizard;
use App\Models\WizardInfo;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/pregnancy-plan-form',
    description: 'common titles - объект с заголовками каждой страницы "визарда". data - массив объектов,
    каждый из которых содержит все вопросы для конкретной страницы. Поле index - нумерация вопросов',
    summary: 'Визард, заголовки страниц, тексты вопросов, вариантов ответов',
    security: [['bearerAuth' => []]],
    tags: ['wizard'])
]
#[OA\Response(response: Response::HTTP_OK, description: 'OK',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'common_titles',
            description: 'объект с заголовками каждой страницы "визарда"',
            type: 'object',
        ),
        new OA\Property(property: 'data',
            description: 'массив объектов, каждый из которых содержит все вопросы для конкретной страницы.
            Поле index - нумерация вопросов/страниц',
            type: 'array',
            items: new OA\Items(example: [
                "index" => 3,
                "title" => "Мои пожелания о родах",
                "form" => [
                    "data" => [
                        "text" => "Мне предстоят роды:",
                        "answers" => []
                    ],
                    "type" => "question-type-radio",
                    "index" => 1
                ]])
        ),
    ])
)]
#[OA\Response(response: Response::HTTP_UNAUTHORIZED, description: 'Unauthorized',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Unauthorized')
    ])
)]
#[OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: 'Server Error')]
class WizardControllerRetrieve extends Controller
{
    public function __invoke()
    {
        $wizardSteps = WizardResource::collection(Wizard::query()->orderBy('order')->get());
        $wizardCommonTitles = WizardInfo::query()->pluck('value', 'key');

        return response()->json([
            'common_titles' => $wizardCommonTitles,
            'data' => $wizardSteps,
        ]);
    }
}
