<?php

namespace App\Http\Controllers\Api\Category;

use App\Http\Resources\CategoryCollection;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/categories',
    description: 'Получение главных категорий',
    summary: 'Получение главных категорий',
    security: [['bearerAuth' => []]],
    tags: ['category'])
]
#[OA\Response(response: 200, description: 'OK',
    content: new OA\JsonContent(
        //        properties: [
        //            new OA\Property(property: 'data', ref: '#/components/schemas/DiplomaResource'),
        //        ],
        example: [
            'data' => [
                [
                    'id' => 1,
                    'title' => 'Беременность',
                    'parent_id' => 0,
                    'slug' => 'beremennost',
                    'description' => 'Minus sit officiis ipsa corrupti corporis sunt deleniti. Amet quam animi voluptatibus omnis. Nihil aut illo mollitia tempora.',
                    'info' => 'Velit tempora voluptatibus dolorem est ab optio quidem. Veniam saepe exercitationem delectus in. Earum possimus explicabo saepe omnis optio rerum iusto qui.',
                    'preview_picture' => 'https://via.placeholder.com/640x480.png/003388?text=quod',
                ],
            ],
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
