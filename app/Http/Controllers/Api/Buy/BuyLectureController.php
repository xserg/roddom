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
use App\Services\PurchaseService;
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
        private PaymentService    $paymentService,
        private PurchaseService   $purchaseService
    ) {
    }

    public function __invoke(
        BuyLectureRequest $request,
        int               $lectureId,
        int               $period
    ) {
        $order = $this->createOrder($request, $lectureId, $period);

        $link = $this->paymentService->createPayment(
            self::coinsToRoubles($order->price_to_pay),
            [
                'order_id' => $order->id,
                'buyer_email' => $order->userEmail(),
                'description' => $this->purchaseService->resolveEntityTitle(
                    $order->subscriptionable_type,
                    $order->subscriptionable_id),
                'amount' => ['value' => self::coinsToRoubles($order->price_to_pay), 'currency' => 'RUB'],
                'quantity' => 1,
            ]
        );

        return response()->json(['link' => $link]);
    }

    public function prepareOrderForTinkoff(
        BuyLectureRequest $request,
        int               $lectureId,
        int               $period
    ) {
        $order = $this->createOrder($request, $lectureId, $period);

        return response()->json([$order->code]);
    }

    private function createOrder(
        BuyLectureRequest $request,
        int               $lectureId,
        int               $period
    ): Order {
        $lecture = $this->lectureRepository->getLectureById($lectureId);

        if ($lecture->isFree()) {
            throw new UserCannotBuyFreeLectureException();
        }

        $isLecturePurchased = $this->lectureService->isLecturePurchased($lectureId);

        if ($isLecturePurchased) {
            throw new UserCannotBuyAlreadyBoughtLectureException();
        }

        $price = $this->lectureService->getLecturePriceForPeriod($lectureId, $period);
        $refPointsToSpend = self::roublesToCoins($request->validated('ref_points', 0));

        return $this->purchaseService->resolveOrder(
            auth()->id(),
            Lecture::class,
            $lectureId,
            $price,
            1,
            $period,
            $refPointsToSpend,
        );
    }
}
