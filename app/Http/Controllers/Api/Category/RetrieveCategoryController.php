<?php

namespace App\Http\Controllers\Api\Category;

use App\Http\Resources\CategoryCollection;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[
    OA\Get(
        path: '/category/{slug}',
        description: "Получение главной категории,
    всех подкатегорий этой категории лекций,
    всех лекторов этой категории",
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
            'category' => [
                "id" => 1,
                "title" => "Беременность",
                "parent_id" => 0,
                "slug" => "beremennost",
                "description" => "Ad odio eligendi quae facere est. Facilis aut enim vel excepturi rem voluptatem aut voluptatibus. Sapiente eveniet beatae at autem ut rerum.",
                "info" => "Expedita incidunt eum sit minima doloremque. Eius magnam explicabo incidunt tempore dolorem et. Laboriosam enim quia tempore. Nam inventore illum illo rerum non et sit.",
                "preview_picture" => "images/categories/category2.jpg",
                "prices" => []
            ],
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
                ],
                [
                    "id" => 12,
                ]
            ],
            'lectors' => [
                "data" => [
                    [
                        "id" => 2,
                        "name" => "Jeanie Weissnat",
                        "position" => "Biophysicist",
                        "description" => "Alias iusto possimus dolores nihil dolor. Omnis est totam distinctio aut veritatis. Asperiores enim est ducimus quidem at a velit. Et quia sit porro doloribus eum dolorem dolorem magni.",
                        "career_start" => "2021-03-21",
                        "photo" => "images/lectors/lector1.jpg",
                        "rates" => [
                            "rate_avg" => null,
                            "rate_user" => null
                        ]
                    ],
                    [
                        "id" => 3,
                        "name" => "Mr. Lawrence DuBuque PhD",
                        "position" => "Camera Repairer",
                        "description" => "Beatae quasi est quisquam est reiciendis. Soluta labore recusandae vero possimus possimus sint dolorem. Sequi aspernatur aut molestias minus magni aperiam consequatur.",
                        "career_start" => "2000-03-21",
                        "photo" => "images/lectors/lector2.jpg",
                        "rates" => [
                            "rate_avg" => null,
                            "rate_user" => null
                        ]
                    ],
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
            ->firstOrFail();

        $subCategories = Category
            ::subCategories()
            ->where('parent_id', '=', $mainCategory->id)
            ->with('lectures.lector')
            ->get();

        return response()->json(
            [
                'category' => new CategoryResource($mainCategory),
                'data' => new CategoryCollection($subCategories),
            ]
        );
    }
}
