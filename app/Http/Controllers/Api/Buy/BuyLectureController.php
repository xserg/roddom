<?php

namespace App\Http\Controllers\Api\Buy;

use App\Exceptions\Custom\UserCannotBuyAlreadyBoughtLectureException;
use App\Exceptions\Custom\UserCannotBuyFreeLectureException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Buy\BuyLectureRequest;
use App\Models\Lecture;
use App\Models\Order;
use App\Repositories\LectureRepository;
use App\Services\LectureService;
use App\Services\PaymentService;
use App\Traits\MoneyConversion;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/lecture/{id}/buy/{period}',
    description: 'Покупка отдельной лекции на период 1, 14, 30 дней',
    summary: 'Покупка отдельной лекции',
    security: [['bearerAuth' => []]],
    tags: ['lecture'])
]
#[OA\Parameter(
    name: 'id',
    description: 'id лекции, которую хотим купить',
    in: 'path',
    required: true,
    schema: new OA\Schema(type: 'integer'),
    example: '137'
)]
#[OA\Parameter(
    name: 'period',
    description: 'на какой срок хотим купить лекцию. Есть три варианта: 1, 14, 30',
    in: 'path',
    required: true,
    schema: new OA\Schema(type: 'integer'),
    example: '14'
)]
#[OA\Response(
    response: Response::HTTP_OK,
    description: 'OK',
    content: new OA\JsonContent(
        example: [
            'link' => 'https://yoomoney.ru/checkout/payments/',
        ]
    )
)]
class BuyLectureController extends Controller
{
    use MoneyConversion;

    public function __construct(
        private LectureRepository $lectureRepository,
        private LectureService    $lectureService,
        private PaymentService    $paymentService
    ) {
    }

    public function __invoke(
        BuyLectureRequest $request,
        int               $lectureId,
        int               $period
    ) {
        $resolved = $this->resolveOrder($request, $lectureId, $period);

        if (! $resolved->order) {
            return response()->json([
                'message' => 'Some problem with order creating'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $link = $this->paymentService->createPayment(
            self::coinsToRoubles(
                $resolved->refPointsToSpend ?
                    $resolved->price - self::roublesToCoins($resolved->refPointsToSpend) :
                    $resolved->price
            ),
            ['order_id' => $resolved->order->id]
        );

        return response()->json([
            'link' => $link,
        ], Response::HTTP_OK);
    }

    public function order(
        BuyLectureRequest $request,
        int               $lectureId,
        int               $period
    ) {
        $resolved = $this->resolveOrder($request, $lectureId, $period);

        if (! $resolved->order) {
            return response()->json([
                'message' => 'Some problem with order creating'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            $resolved->order->code
        ]);
    }

    private function resolveOrder(
        BuyLectureRequest $request,
        int               $lectureId,
        int               $period
    ) {
        $lecture = $this->lectureRepository->getLectureById($lectureId);
        $isLecturePurchased = $this->lectureService->isLecturePurchased($lectureId);
        $price = $this->lectureService->calculateLecturePrice($lecture, $period);

        if ($isLecturePurchased) {
            throw new UserCannotBuyAlreadyBoughtLectureException();
        }

        if ($lecture->isFree()) {
            throw new UserCannotBuyFreeLectureException();
        }

        $refPointsToSpend = $request->validated('ref_points');

        if ($refPointsToSpend && (($price - self::roublesToCoins($refPointsToSpend)) < 100)) {
            $refPointsToSpend = self::coinsToRoubles($price - 100);
        }

        $order = Order::create([
            'user_id' => auth()->id(),
            'price' => $price,
            'points' => self::roublesToCoins($refPointsToSpend ?? 0),
            'subscriptionable_type' => Lecture::class,
            'subscriptionable_id' => $lectureId,
            'period' => $period,
        ]);

        return (object) [
            'order' => $order,
            'price' => $price,
            'refPointsToSpend' => $refPointsToSpend
        ];
    }
}
