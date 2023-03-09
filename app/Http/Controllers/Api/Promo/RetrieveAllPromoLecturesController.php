<?php

namespace App\Http\Controllers\Api\Promo;

use App\Http\Controllers\Controller;
use App\Http\Resources\LectureCollection;
use App\Models\Promo;
use App\Repositories\LectureRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[
    OA\Get(
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
#[OA\Response(
    response: Response::HTTP_OK,
    description: 'OK',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'prices', description: 'цены за весь акционный пак(за все лекции входящие в него), на три периода', type: 'array'),
        new OA\Property(property: 'data', ref: '#/components/schemas/LectureResource'),
    ],
        example: [
            "prices" => [
                [
                    "title" => "day",
                    "length" => 1,
                    "price" => "1,037.73"
                ],
                [
                    "title" => "week",
                    "length" => 14,
                    "price" => "2,058.62"
                ],
                [
                    "title" => "month",
                    "length" => 30,
                    "price" => "3,131.13"
                ]
            ],
            "data" => [
                [
                    "id" => 5,
                    "lector_id" => 16,
                ]
            ]
        ]
    )
)]
#[OA\Response(response: Response::HTTP_UNAUTHORIZED, description: 'Unauthenticated')]
#[OA\Response(response: Response::HTTP_NOT_FOUND, description: 'Not Found')]
#[OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: 'Server Error')]
class RetrieveAllPromoLecturesController extends Controller
{
    public function __construct(
        private LectureRepository $lectureRepository,
    )
    {
    }

    public function __invoke(Request $request)
    {
        try {
            $builder = $this->lectureRepository->getAllPromoQuery(['lector', 'lector.diplomas']);
            $lectures = $this->lectureRepository->paginate(
                $builder,
                $request->per_page,
                $request->page
            );
            /**
             * @var $promo Promo
             */

            $promo = Promo::first();
            $periods = $promo->subscriptionPeriodsForPromoPack;
            $prices = [];
            foreach ($periods as $period) {
                $prices[] = [
                    'title' => $period->title,
                    'length' => $period->length,
                    'price' => number_format($period->pivot->price / 100, 2)
                ];
            }

        } catch (NotFoundHttpException $exception) {

            return response()->json(
                ['message' => $exception->getMessage()],
                Response::HTTP_NOT_FOUND
            );
        }
        return response()->json(
            [
                'prices' => $prices,
                ...(new LectureCollection($lectures))
                    ->response()
                    ->getData(true)
            ]
        );
    }
}
