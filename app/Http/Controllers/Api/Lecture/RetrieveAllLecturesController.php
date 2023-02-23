<?php

namespace App\Http\Controllers\Api\Lecture;

use App\Http\Resources\LectureCollection;
use App\Repositories\LectureRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[OA\Get(
    path: '/lectures',
    description: "Получение ресурсов лекций, с пагинацией",
    summary: "Получение ресурсов лекций",
    security: [["bearerAuth" => []]],
    tags: ["lecture"])
]
#[OA\Parameter(
    parameter: 'per_page',
    name: 'per_page',
    description: 'количество лекций на странице(в одном json\'е)',
    in: 'query',
    example: '15'
)]
#[OA\Parameter(
    parameter: 'page',
    name: 'page',
    description: 'номер страницы, если их несколько',
    in: 'query',
    example: '2'
)]
#[OA\Parameter(
    parameter: 'category',
    name: 'category',
    description: 'Slug категории лекций, которые мы хотим получить',
    in: 'query',
    example: 'ginekologiia',
)]
#[OA\Parameter(
    parameter: 'lector',
    name: 'lector',
    description: 'Id лектора, лекции которого мы хотим получить',
    in: 'query',
    example: '12',
)]
#[OA\Response(response: 200, description: 'OK',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data', ref: '#/components/schemas/LectureResource'),
    ],
        example: [
            "data" => [
                [
                    "id" => 0,
                    "lector_id" => 0,
                    "category_id" => 0,
                    "title" => "string",
                    "preview_picture" => "string",
                    "video_id" => 0
                ],
            ],
            "meta" => [
                "current_page" => 3,
                "from" => 31,
                "last_page" => 10,
                "links" => [],
            ],
            "links" => [],
            "path" => "https://url/v1/lectures",
            "per_page" => 15,
            "to" => 45,
            "total" => 150
        ]
    )
)]
#[OA\Response(response: 401, description: 'Unauthenticated')]
#[OA\Response(response: 404, description: 'Not Found')]
#[OA\Response(response: 500, description: 'Server Error')]
class RetrieveAllLecturesController
{
    public function __construct(
        private LectureRepository $repository
    )
    {
    }

    public function __invoke(Request $request): ResourceCollection|JsonResponse
    {
        try {
            $lectures = $this->repository->getAllWithPaginator(
                $request->per_page,
                $request->page,
                $request->category,
                $request->lector,
            );
        } catch (NotFoundHttpException $exception) {
            return response()->json(
                ['message' => $exception->getMessage()],
                Response::HTTP_NOT_FOUND
            );
        }

        return LectureCollection::make($lectures);
    }
}
