<?php

namespace App\Http\Controllers\Api\Lector;

use App\Http\Controllers\Controller;
use App\Http\Resources\LectorCollection;
use App\Repositories\CategoryRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/lectors/category/{slug}',
    description: 'Получение лекторов конкретной категории товаров',
    summary: 'Получение лекторов конкретной категории товаров',
    security: [['bearerAuth' => []]],
    tags: ['lector'])
]
#[OA\Parameter(
    name: 'slug',
    description: 'slug категории товаров, лекторов которой мы хотим получить',
    in: 'path',
    required: true,
    schema: new OA\Schema(type: 'string')
)]
#[OA\Response(response: Response::HTTP_OK, description: 'OK',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data', ref: '#/components/schemas/LectorResource'),
    ])
)]
#[OA\Response(
    response: Response::HTTP_INTERNAL_SERVER_ERROR,
    description: 'Server Error',
)]
#[OA\Response(
    response: Response::HTTP_NOT_FOUND,
    description: 'Not Found',
)]
class RetrieveLectorsByCategoryController extends Controller
{
    public function __construct(
        private CategoryRepository $categoryRepository
    ) {
    }

    public function __invoke(Request $request, string $slug)
    {
        $lectors = $this->categoryRepository->getAllLectorsByCategory($slug);

        if ($lectors->isEmpty()) {
            return response()->json(['data' => []], Response::HTTP_NOT_FOUND);
        }

        return response()->json(new LectorCollection($lectors));
    }
}
