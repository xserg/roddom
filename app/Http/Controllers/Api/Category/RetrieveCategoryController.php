<?php

namespace App\Http\Controllers\Api\Category;

use App\Http\Resources\CategoryCollection;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/category/{slug}',
    description: "Получение всех подкатегорий определенной категории лекций",
    summary: "Получение подкатегорий",
    security: [["bearerAuth" => []]],
    tags: ["category"])
]
#[OA\Parameter(
    name: 'slug',
    description: 'slug главной категории, подкатегории которой хотим получить',
    in: 'path',
    required: true,
    schema: new OA\Schema(type: 'string'),
    example: 'ginekologiia'
)]
#[OA\Response(response: 200, description: 'OK',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'data', ref: '#/components/schemas/CategoryResource'),
        ],
        example: [
            'data' => [
                [
                    "id" => 8,
                    "title" => "Название подкатегории - 1",
                    "parent_id" => 5,
                    "parent_slug" => "ginekologiia",
                    "slug" => "nazvanie-podkategorii-1",
                    "description" => "Tenetur et vel quis sit ex illo. Qui omnis minima inventore. Animi iste aut ducimus consequuntur est.",
                    "info" => "Hic repellendus aut nihil est et. Eum quasi deleniti consequatur et dolorem tempore. Modi aliquid rem consequuntur quibusdam doloremque tempora. Sit id voluptatem commodi et rerum quaerat.",
                    "preview_picture" => "https://url/storage/images/categories/1.png"
                ]
            ]
        ]),

)]
#[OA\Response(response: 401, description: 'Unauthenticated')]
#[OA\Response(response: 404, description: 'Not Found')]
#[OA\Response(response: 500, description: 'Server Error')]
class RetrieveCategoryController
{
    public function __invoke(Request $request, string $slug): JsonResponse
    {
        $mainCategory = Category
            ::query()
            ->where('slug', '=', $slug)
            ->first();

        if (!$mainCategory) {
            return response()->json([
                'message' => 'Не найдена категория со слагом: ' . $slug
            ], 404);
        }

        return response()->json(
            ['category' => new CategoryResource($mainCategory),
                'data' => new CategoryCollection(
                    Category
                        ::subCategories()
                        ->where('parent_id', '=', $mainCategory->id)
                        ->get()
                )
            ]
        );
    }
}
