<?php

namespace App\Http\Controllers\Api\Promo;

use App\Http\Controllers\Controller;
use App\Http\Resources\LectureCollection;
use App\Repositories\PromoRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[OA\Get(
    path: '/promopack',
    description: "Получение всех акционных лекций, с пагинацией",
    summary: "Получение всех акционных лекций",
    security: [["bearerAuth" => []]],
    tags: ["promo"])
]
#[OA\Parameter(
    name: 'per_page',
    description: 'количество лекций на странице(в одном json\'е) 15 по дефолту',
    in: 'query',
    example: '15'
)]
#[OA\Parameter(
    name: 'page',
    description: 'номер страницы, если их несколько',
    in: 'query',
    example: '2'
)]
#[OA\Response(response: Response::HTTP_UNAUTHORIZED, description: 'Unauthenticated')]
#[OA\Response(response: Response::HTTP_NOT_FOUND, description: 'Not Found')]
#[OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: 'Server Error')]
class RetrieveAllPromoLecturesController extends Controller
{
    public function __construct(
        private PromoRepository $promoRepository
    )
    {
    }

    public function __invoke(Request $request)
    {
        try {
            $lectures = $this->promoRepository->getAllWithPaginator(
                $request->per_page,
                $request->page,
            );
        } catch (NotFoundHttpException $exception) {

            return response()->json(
                ['message' => $exception->getMessage()],
                Response::HTTP_NOT_FOUND
            );
        }
        return response()->json(
            (new LectureCollection($lectures))
                ->response()
                ->getData(true)
        );
    }
}
