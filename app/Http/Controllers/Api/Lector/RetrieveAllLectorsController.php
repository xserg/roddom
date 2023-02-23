<?php

namespace App\Http\Controllers\Api\Lector;

use App\Http\Resources\LectorCollection;
use App\Repositories\LectorRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use OpenApi\Attributes as OA;


#[OA\Get(
    path: '/lectors',
    description: "Получение ресурсов лекторов",
    summary: "Получение ресурсов лекторов",
    security: [["bearerAuth" => []]],
    tags: ["lector"]
),
]
#[OA\Response(
    response: 200,
    description: 'OK',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(
                property: 'data',
                type: 'array',
                items: new OA\Items('#/components/schemas/LectorResource')),
        ],
        example: [
            "data" => [
                [
                    "id" => 0,
                    "name" => "string",
                    "career_start" => "2023-02-21",
                    "photo" => "string",
                ],
            ]
        ])
)]
#[OA\Response(response: 500, description: 'Server Error')]
class RetrieveAllLectorsController
{
    public function __construct(
        private LectorRepository $repository
    )
    {
    }

    public function __invoke(Request $request): ResourceCollection
    {
        $perPage = $request->per_page;
        $page = $request->page;

        return new LectorCollection(
            $this->repository->getAllWithPaginator($perPage, $page)
        );
    }
}
