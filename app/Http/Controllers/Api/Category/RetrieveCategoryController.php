<?php

namespace App\Http\Controllers\Api\Category;

use App\Http\Resources\CategoryCollection;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[
    OA\Get(
        path: '/category/{slug}',
        description: 'Получение главной категории,
                      всех подкатегорий этой категории лекций,
                      всех лекторов этой категории',
        summary: 'Получение подкатегорий',
        security: [['bearerAuth' => []]],
        tags: ['category'])
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
                'id' => 1,
                'title' => 'Беременность',
                'parent_id' => 0,
                'slug' => 'beremennost',
                'description' => 'Ad odio eligendi quae facere est. Facilis aut enim vel excepturi rem voluptatem aut voluptatibus. Sapiente eveniet beatae at autem ut rerum.',
                'info' => 'Expedita incidunt eum sit minima doloremque. Eius magnam explicabo incidunt tempore dolorem et. Laboriosam enim quia tempore. Nam inventore illum illo rerum non et sit.',
                'preview_picture' => 'images/categories/category2.jpg',
                'prices' => [],
            ],
            'data' => [
                [
                    'id' => 8,
                    'title' => 'Название подкатегории - 1',
                    'parent_id' => 5,
                    'parent_slug' => 'ginekologiia',
                    'slug' => 'nazvanie-podkategorii-1',
                    'description' => 'Tenetur et vel quis sit ex illo. Qui omnis minima inventore. Animi iste aut ducimus consequuntur est.',
                    'info' => 'Hic repellendus aut nihil est et. Eum quasi deleniti consequatur et dolorem tempore. Modi aliquid rem consequuntur quibusdam doloremque tempora. Sit id voluptatem commodi et rerum quaerat.',
                    'preview_picture' => 'https://url/storage/images/categories/1.png',
                    'prices' => [
                        [
                            'title' => 'day',
                            'length' => 1,
                            'price_for_one_lecture' => 115.44,
                            'price_for_category' => 215.43,
                        ],
                        [
                            'title' => 'week',
                            'length' => 14,
                            'price_for_one_lecture' => 313.52,
                            'price_for_category' => 413.51,
                        ],
                        [
                            'title' => 'month',
                            'length' => 30,
                            'price_for_one_lecture' => 514.72,
                            'price_for_category' => 614.71,
                        ],
                    ],
                ],
                [
                    'id' => 12,
                    'etc' => 'etc',
                ],
            ],
        ]),

)]
#[OA\Response(response: 401, description: 'Unauthenticated')]
#[OA\Response(response: 404, description: 'Not Found')]
#[OA\Response(response: 500, description: 'Server Error')]
class RetrieveCategoryController
{
    public function __construct(
        private CategoryService $categoryService
    ) {
    }

    public function __invoke(Request $request, string $slug): JsonResponse
    {
        /**
         * @var Category $mainCategory
         */
        $mainCategory = Category::query()
            ->where('slug', '=', $slug)
            ->where('parent_id', 0)
            ->with([
                'childrenCategories.categoryPrices.period',
                'childrenCategories.parentCategory',
                'childrenCategories.categoryPrices',
                'childrenCategories.lectures.category.categoryPrices',
                'childrenCategories.lectures.pricesInPromoPacks',
                'childrenCategories.lectures.pricesForLectures',
                'childrenCategories.lectures.pricesPeriodsInPromoPacks',
                'childrenCategories.lectures.paymentType',
                'childrenCategories.lectures.contentType',
            ])
            ->first();

        if (is_null($mainCategory)) {
            $subCategory = Category::query()
                ->where('slug', '=', $slug)
                ->with([
                    'categoryPrices.period',
                    'parentCategory',
                    'categoryPrices',
                    'lectures.category.categoryPrices',
                    'lectures.pricesInPromoPacks',
                    'lectures.pricesForLectures',
                    'lectures.pricesPeriodsInPromoPacks',
                    'lectures.paymentType',
                    'lectures.contentType',
                ])
                ->first();
            $prices = $this->categoryService->formSubCategoryPrices($subCategory);
            $subCategory->prices = $prices;
            return response()->json(
                [
                    'category' => new CategoryResource($subCategory),
                    'data' => [],
                ]
            );
        }

        $prices = $this->categoryService->formMainCategoryPrices($mainCategory);
        $mainCategory->prices = $prices;

        foreach ($mainCategory->childrenCategories as $subCategory) {
            $prices = $this->categoryService->formSubCategoryPrices($subCategory);
            $subCategory->prices = $prices;
        }

        return response()->json(
            [
                'category' => new CategoryResource($mainCategory),
                'data' => new CategoryCollection($mainCategory->childrenCategories),
            ]
        );
    }
}
