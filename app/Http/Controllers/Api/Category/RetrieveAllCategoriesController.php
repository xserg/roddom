<?php

namespace App\Http\Controllers\Api\Category;

use App\Http\Resources\CategoryCollection;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/categories',
    description: "Получение ресурсов главных категорий лекций",
    summary: "Получение ресурсов главных категорий лекций",
    security: [["bearerAuth" => []]],
    tags: ["lecture"])
]
#[OA\Response(response: 200, description: 'OK',
    content: new OA\JsonContent(
//        properties: [
//            new OA\Property(property: 'data', ref: '#/components/schemas/DiplomaResource'),
//        ],
        example: [
            'data' => [
                [
                    "id" => 5,
                    "parent_id" => 0,
                    "title" => "Гинекология",
                    "slug" => "ginekologiia",
                    "description" => "Nihil non eius eos ullam velit harum dolorem. Rerum explicabo quis ab non. Nulla hic nostrum ipsum nihil eum.",
                    "info" => "Expedita explicabo excepturi a expedita eos magni sit sapiente. Tempora molestiae ut nobis eum. Et molestiae ad ut et esse consequatur voluptatum. Excepturi et suscipit corporis nobis.",
                    "created_at" => null,
                    "updated_at" => null
                ]
            ]
        ]),

)]
#[OA\Response(response: 401, description: 'Unauthenticated')]
#[OA\Response(response: 500, description: 'Server Error')]
class RetrieveAllCategoriesController
{
    public function __invoke(): JsonResponse
    {
        return response()->json(
            new CategoryCollection(
                Category::mainCategories()->get()
            ), Response::HTTP_OK
        );
    }
}
