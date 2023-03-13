<?php

namespace App\Http\Controllers\Api\Buy;

use App\Http\Controllers\Controller;
use App\Http\Requests\Buy\BuyPromoRequest;
use App\Models\Order;
use App\Models\Promo;
use App\Repositories\PromoRepository;
use App\Services\PaymentService;
use App\Services\PromoService;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/promopack/buy/{period}',
    description: "Покупка промо пака на период 1, 14, 30 дней",
    summary: "Покупка промо пака",
    security: [["bearerAuth" => []]],
    tags: ["promo"])
]
#[OA\Parameter(
    name: 'period',
    description: 'на какой срок хотим купить промо пак(и все его лекции соответсвенно). Есть три варианта: 1, 14, 30',
    in: 'path',
    required: true,
    schema: new OA\Schema(type: 'integer'),
    example: '30'
)]
#[OA\Response(
    response: Response::HTTP_OK,
    description: 'OK',
    content: new OA\JsonContent(
        example: [
            "link" => "https://yoomoney.ru/checkout/payments/"
        ]
    )
)]
class BuyPromoController extends Controller
{
    public function __construct(
        private PromoService    $promoService,
        private PromoRepository $promoRepository,
        private PaymentService  $paymentService
    )
    {
    }

    public function __invoke(
        BuyPromoRequest $request,
        int             $periodLength
    )
    {
        $isPurchased = $this->promoService->isPromoPurchased();

        if ($isPurchased) {
            return response()->json([
                'message' => 'Promo pack is already purchased.'
            ], Response::HTTP_FORBIDDEN);
        }

        $promoPack = Promo::first();
        $price = $this->promoRepository->getPriceForExactPeriodLength($promoPack, $periodLength);

        $order = Order::create([
            'user_id' => auth()->user()->id,
            'price' => $price,
            'subscriptionable_type' => Promo::class,
            'subscriptionable_id' => $promoPack->id,
            'period' => $periodLength
        ]);

        if ($order) {
            $link = $this->paymentService->createPayment(
                $price,
                ['order_id' => $order->id]
            );

            return response()->json([
                'link' => $link
            ], Response::HTTP_OK);
        }
    }
}
