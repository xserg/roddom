<?php

namespace App\Http\Controllers\Api\Promo;

use App\Http\Controllers\Controller;
use App\Http\Resources\LectureCollection;
use App\Models\Promo;
use App\Repositories\LectureRepository;
use App\Repositories\PromoRepository;
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
#[OA\Parameter(
    name: 'filter[lector_id]',
    description: 'пишем filter[lector_id]=12,25 id лектора/ов, лекции которого мы хотим получить',
    in: 'query',
    example: '12,25,1',
)]
#[OA\Parameter(
    name: 'filter[category_id]',
    description: 'пишем filter[category_id]=21,11 id подкатегории/ий, лекции которых мы хотим получить',
    in: 'query',
    example: '21,11',
)]
#[OA\Parameter(
    name: 'filter[watched]',
    description: 'пишем filter[watched]=1 получаем просмотренные пользователем лекции',
    in: 'query',
    example: '1',
)]
#[OA\Parameter(
    name: 'filter[saved]',
    description: 'пишем filter[saved]=1 получаем сохраненные пользователем лекции',
    in: 'query',
    example: '1',
)]
#[OA\Parameter(
    name: 'filter[purchased]',
    description: 'пишем filter[purchased]=1 получаем купленные пользователем лекции',
    in: 'query',
    example: '1',
)]
#[OA\Parameter(
    name: 'filter[not_watched]',
    description: 'пишем filter[not_watched]=1 получаем не просмотренные юзером лекции',
    in: 'query',
    example: '1',
)]
#[OA\Parameter(
    name: 'include',
    description: 'включаем в объект каждой лекции соответствующие
    объекты категории или лектора этой лекции или оба.
    Также можно lector.diplomas, чтобы дипломы захватить у лектора',
    in: 'query',
    example: 'category,lector,lector.diplomas',
)]
#[OA\Parameter(
    name: 'sort',
    description: 'сортировка по полю created_at. Возможные варианты sort=-created_at или sort=created_at.
    По дефолту -created_at - лекции добавленные последними, идут первыми',
    in: 'query',
    example: '-created_at',
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
        private PromoRepository   $promoRepository
    )
    {
    }

    public function __invoke(Request $request)
    {
//        try {
            $builder = $this->lectureRepository->getAllPromoQuery();
            $builder = $this->lectureRepository->addFiltersToQuery($builder);
            $lectures = $this->lectureRepository->paginate(
                $builder,
                $request->per_page,
                $request->page
            );

            /**
             * @var $promo Promo
             */
            $promo = Promo::first();
            $prices = $this->promoRepository->getPrices($promo);

//        } catch (NotFoundHttpException $exception) {
//
//            return response()->json(
//                ['message' => $exception->getMessage()],
//                Response::HTTP_NOT_FOUND
//            );
//        }
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
