<?php

namespace App\Http\Controllers\Api\Buy;

use App\Exceptions\Custom\UserCannotBuyAlreadyBoughtPromoPackException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Buy\BuyPromoRequest;
use App\Models\Lecture;
use App\Models\Order;
use App\Models\Promo;
use App\Repositories\PeriodRepository;
use App\Repositories\PromoRepository;
use App\Services\PaymentService;
use App\Services\PromoService;
use App\Services\PurchaseService;
use App\Traits\MoneyConversion;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Post(
    path: '/promopack/buy/{period}',
    description: 'Покупка промо пака на период 1, 14, 30 дней',
    summary: 'Покупка промо пака',
    security: [['bearerAuth' => []]],
    tags: ['promo'])
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
            'link' => 'https://yoomoney.ru/checkout/payments/',
        ]
    )
)]
class BuyPromoController extends Controller
{
    use MoneyConversion;

    public function __construct(
        private PromoService     $promoService,
        private PromoRepository  $promoRepository,
        private PeriodRepository $periodRepository,
        private PaymentService   $paymentService,
        private PurchaseService  $purchaseService
    ) {
    }

    public function __invoke(
        BuyPromoRequest $request,
        int             $periodLength
    ) {
        $order = $this->createOrder($request, $periodLength);

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
        BuyPromoRequest $request,
        int             $periodLength
    ) {
        $order = $this->createOrder($request, $periodLength);

        return response()->json([$order->code]);
    }

    private function createOrder(
        BuyPromoRequest $request,
        int             $periodLength,
        int             $promoPackId = 1
    ): Order {
        $lectures = $this->promoRepository->getAllLecturesForPromoPack($promoPackId);
        $isPurchased = $this->promoService->isPromoPurchased($promoPackId);

        if ($isPurchased) {
            throw new UserCannotBuyAlreadyBoughtPromoPackException();
        }

        $periodId = $this->periodRepository->getPeriodByLength($periodLength)->id;
        $price = $this->promoRepository->getPromoPackPriceForPeriod($promoPackId, $periodId);

        $refPointsToSpend = self::roublesToCoins($request->validated('ref_points', 0));

        return $this->purchaseService->resolveOrder(
            auth()->id(),
            Promo::class,
            1,
            $price,
            Lecture::promo()->count(),
            $periodLength,
            $refPointsToSpend
        );
    }
}
