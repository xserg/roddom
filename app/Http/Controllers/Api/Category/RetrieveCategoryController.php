<?php

namespace App\Http\Controllers\Api\Category;

use App\Http\Resources\CategoryCollection;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/category/{slug}',
    description: "Получение всех подкатегорий определенной категории лекций",
    summary: "Получение подкатегорий",
    security: [["bearerAuth" => []]],
    tags: ["lecture"])
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
                    "id" => 23,
                    "parent_id" => 5,
                    "title" => "Название подкатегории - 17",
                    "slug" => "nazvanie-podkategorii-17",
                    "description" => "In molestiae quae et recusandae nisi. Nihil eum non ut possimus voluptatum aut dolorem assumenda. Suscipit quis suscipit placeat qui provident.",
                    "info" => "Accusantium mollitia et itaque vero quia. Velit eos quis autem enim et. Neque voluptas mollitia maiores optio aperiam molestias.",
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
                'message' => 'Not found category with slug: ' . $slug
            ], 404);
        }

        return response()->json(
            new CategoryCollection(
                Category
                    ::subCategories()
                    ->where('parent_id', '=', $mainCategory->id)
                    ->get()
            )
        );
    }
}
