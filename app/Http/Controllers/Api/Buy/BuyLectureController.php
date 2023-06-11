<?php

namespace App\Http\Controllers\Api\Buy;

use App\Http\Controllers\Controller;
use App\Http\Requests\Buy\BuyLectureRequest;
use App\Models\Lecture;
use App\Models\Order;
use App\Repositories\LectureRepository;
use App\Services\LectureService;
use App\Services\PaymentService;
use App\Traits\MoneyConversion;
use Illuminate\Http\Response;
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
        private LectureService $lectureService,
        private PaymentService $paymentService
    ) {
    }

    public function __invoke(
        BuyLectureRequest $request,
        int $lectureId,
        int $period
    ) {
        $lecture = $this->lectureRepository->getLectureById($lectureId);
        $isPurchasedStrict = $this->lectureService->isLectureStrictPurchased($lectureId, auth()->user());
        $price = self::coinsToRoubles($this->lectureService->calculateLecturePrice($lecture, $period));

        if ($isPurchasedStrict) {
            return response()->json([
                'message' => 'Lecture with id '.$request->id.' is already purchased.',
            ], Response::HTTP_FORBIDDEN);
        }

        //        $isFree = $this->lectureService->isFree($lectureId);

        //        if ($isFree) {
        //            return response()->json([
        //                'message' => 'You cannot purchase free lecture'
        //            ], Response::HTTP_FORBIDDEN);
        //        }

        $order = Order::create([
            'user_id' => auth()->user()->id,
            'price' => $price,
            'subscriptionable_type' => Lecture::class,
            'subscriptionable_id' => $lectureId,
            'period' => $period,
        ]);

        if ($order) {
            $link = $this->paymentService->createPayment(
                $price,
                ['order_id' => $order->id]
            );

            return response()->json([
                'link' => $link,
            ], Response::HTTP_OK);
        }
    }
}
