<?php

namespace App\Http\Controllers\Api\Lecture;

use App\Http\Resources\LectureCollection;
use App\Repositories\LectureRepository;
use App\Services\LectureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[OA\Get(
    path: '/lectures',
    description: 'Получение ресурсов лекций, с пагинацией',
    summary: 'Получение ресурсов лекций',
    security: [['bearerAuth' => []]],
    tags: ['lecture'])
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
    name: 'filter[list-watched]',
    description: 'пишем filter[list-watched]=1 получаем добавленные в список просмотренных пользователем лекции',
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
    name: 'filter[recommended]',
    description: 'пишем filter[recommended]=1 получаем отмеченные рекомендованными лекции',
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
        new OA\Property(property: 'data', ref: '#/components/schemas/LectureResource'),
    ],
        example: [
            'data' => [
                [
                    'id' => 100,
                    'lector_id' => 10,
                    'category_id' => 28,
                    'title' => 'Dolor alias nam impedit deserunt.',
                    'description' => 'Error sint asperiores eum magni quis. Harum officiis iste impedit debitis facilis.',
                    'preview_picture' => 'https://via.placeholder.com/640x480.png/004477?text=ut',
                    'lector' => [],
                    'is_free' => 1,
                    'is_promo' => 0,
                    'is_watched' => 1,
                    'purchase_info' => [
                        'is_purchased' => 1,
                        'end_date' => '2023-03-09 22:32:12',
                    ],
                    'prices' => [
                        'price_by_category' => [
                            [
                                'title' => 'day',
                                'length' => 1,
                                'price_for_lecture' => '109.60',
                            ],
                            [
                                'title' => 'week',
                                'length' => 14,
                                'price_for_lecture' => '302.48',
                            ],
                            [
                                'title' => 'month',
                                'length' => 30,
                                'price_for_lecture' => '519.83',
                            ],
                        ],
                        'price_by_promo' => [
                            [
                                'title' => 'day',
                                'length' => 1,
                                'price_for_promo_lecture' => '106.51',
                            ],
                            [
                                'title' => 'week',
                                'length' => 14,
                                'price_for_promo_lecture' => '209.06',
                            ],
                            [
                                'title' => 'month',
                                'length' => 30,
                                'price_for_promo_lecture' => '323.79',
                            ],
                        ],
                    ],
                ],
            ],
            'meta' => [
                'current_page' => 3,
                'from' => 31,
                'last_page' => 10,
                'links' => [],
            ],
            'links' => [],
            'path' => 'https://url/v1/lectures',
            'per_page' => 15,
            'to' => 45,
            'total' => 150,
        ]
    )
)]
#[OA\Response(response: Response::HTTP_UNAUTHORIZED, description: 'Unauthenticated')]
#[OA\Response(response: Response::HTTP_NOT_FOUND, description: 'Not Found')]
#[OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: 'Server Error')]
class RetrieveAllLecturesController
{
    public function __construct(
        private LectureRepository $lectureRepository,
        private LectureService    $lectureService
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $relations = [
                'category.parentCategory',
                'category.categoryPrices',
                'contentType',
                'paymentType',
                'pricesPeriodsInPromoPacks',
                'pricesForLectures',
                'rates',
                'userRate',
                'averageRate',
                'watchedUsers',
                'savedUsers',
                'listWatchedUsers'
            ];

            $builder = $this->lectureRepository->getAllQueryWith($relations);
            $builder = $this->lectureRepository->addFiltersToQuery($builder);
            $lectures = $builder->get()->append('prices');
            $lectures = $this->lectureService->setPurchaseInfoToLectures($lectures);
            $lectures = $this->lectureRepository->paginate(
                $lectures,
                (int) $request->per_page ?? 15,
                $request->page,
            );
        } catch (NotFoundHttpException $exception) {
            return response()->json(
                ['message' => $exception->getMessage()],
                Response::HTTP_NOT_FOUND
            );
        }

        return response()->json(
            (new LectureCollection($lectures))->response()->getData(true),
            Response::HTTP_OK
        );
    }
}
