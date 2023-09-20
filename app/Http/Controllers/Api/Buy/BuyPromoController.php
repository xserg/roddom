<?php

namespace App\Http\Controllers\Api\Buy;

use App\Exceptions\Custom\PromoLecturesAreEmptyException;
use App\Exceptions\Custom\UserCannotBuyAlreadyBoughtPromoPackException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Buy\BuyPromoRequest;
use App\Models\Order;
use App\Models\Promo;
use App\Repositories\PeriodRepository;
use App\Repositories\PromoRepository;
use App\Services\PaymentService;
use App\Services\PromoService;
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
        private PaymentService   $paymentService
    ) {
    }

    public function __invoke(
        BuyPromoRequest $request,
        int             $periodLength
    ) {
        $resolved = $this->resolveOrder($request, $periodLength);

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
        BuyPromoRequest $request,
        int             $periodLength
    ) {
        $resolved = $this->resolveOrder($request, $periodLength);

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
        BuyPromoRequest $request,
        int             $periodLength
    ) {
        $isPurchased = $this->promoService->isPromoPurchased();

        if ($isPurchased) {
            throw new UserCannotBuyAlreadyBoughtPromoPackException();
        }

        $promoLectures = $this->promoRepository->getAllLectures();

        if ($promoLectures->isEmpty()) {
            throw new PromoLecturesAreEmptyException();
        }

        $periodId = $this->periodRepository
            ->getPeriodByLength($periodLength)
            ->id;
        $prices = $this->promoRepository
            ->calculatePromoPackPriceForPeriod(1, $periodId);
        $price = $prices['final_price'];

        $refPointsToSpend = $request->validated('ref_points', 0);

        if ($refPointsToSpend && (($price - self::roublesToCoins($refPointsToSpend)) < 100)) {
            $refPointsToSpend = self::coinsToRoubles($price - 100);
        }

        $order = Order::create([
            'user_id' => auth()->user()->id,
            'price' => $price,
            'points' => self::roublesToCoins($refPointsToSpend),
            'subscriptionable_type' => Promo::class,
            'subscriptionable_id' => 1,
            'period' => $periodLength,
        ]);

        return (object) [
            'order' => $order,
            'price' => $price,
            'refPointsToSpend' => $refPointsToSpend
        ];
    }
}
