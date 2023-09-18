<?php

namespace App\Http\Controllers\Api\Buy;

use App\Http\Requests\Buy\BuyAllLecturesRequest;
use App\Models\EverythingPack;
use App\Models\FullCatalogPrices;
use App\Models\Order;
use App\Models\Period;
use App\Services\LectureService;
use App\Services\PaymentService;
use App\Traits\MoneyConversion;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Http;

class BuyAllLecturesController
{
    use MoneyConversion;

    public function __construct(
        private LectureService $lectureService,
        private PaymentService $paymentService
    ) {
    }

    public function __invoke(
        BuyAllLecturesRequest $request,
        int                   $periodLength
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
        BuyAllLecturesRequest $request,
        int                   $periodLength
    ) {
        $resolved = $this->resolveOrder($request, $periodLength);

        if (! $resolved->order) {
            return response()->json([
                'message' => 'Some problem with order creating'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

//        $response = Http::post('https://forma.tinkoff.ru/api/partners/v2/orders/create-demo', [
//            "shopId" => config('services.tinkoff.shop_id'),
//            "showcaseId" => config('services.tinkoff.case_id'),
//            "items" => [
//                ["name" => "Hasta", "quantity" => 1, "price" => self::coinsToRoubles($resolved->price)]
//            ],
//            "demoFlow" => "sms",
//            "orderNumber" => $resolved->order->code,
//            "sum" => self::coinsToRoubles($resolved->price),
//            "values" => [
//                "contact" => [
//                    "fio" => [
//                        "lastName" => "Иванов",
//                        "firstName" => "Иван",
//                        "middleName" => "Иванович"
//                    ],
//                    "mobilePhone" => "9998887766",
//                    "email" => "ivan@example.com"
//                ]
//            ]
//        ]);

        return response()->json([
//            'link' => $response->object()->link,
//            'id' =>
                $resolved->order->code
        ]);
    }

    private function resolveOrder(
        BuyAllLecturesRequest $request,
        int                   $periodLength
    ) {
        $period = Period::firstWhere('length', $periodLength);
        $refPointsToSpend = $request->validated('ref_points');
        $fullCatalogPrices = FullCatalogPrices::with('period')->get();
        $fullCatalogPricesForPeriod = $fullCatalogPrices->firstWhere('period_id', $period->id);

        if ($fullCatalogPricesForPeriod->is_promo) {
            $price = $this->lectureService->calculateEverythingPricePromoByPeriod($fullCatalogPricesForPeriod);
        } else {
            $price = $this->lectureService->calculateEverythingPriceByPeriod($fullCatalogPricesForPeriod);
        }

        if ($refPointsToSpend && (($price - self::roublesToCoins($refPointsToSpend)) < 100)) {
            $refPointsToSpend = self::coinsToRoubles($price - 100);
        }

        return (object) [
            'order' => Order::create([
                'user_id' => auth()->id(),
                'price' => $price,
                'points' => self::roublesToCoins($refPointsToSpend ?? 0),
                'subscriptionable_type' => EverythingPack::class,
                'subscriptionable_id' => 1,
                'period' => $periodLength,
            ]),
            'price' => $price,
            'refPointsToSpend' => $refPointsToSpend
        ];
    }
}
