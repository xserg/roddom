<?php

namespace App\Http\Controllers\Api\Buy;

use App\Http\Requests\Buy\BuyAllLecturesRequest;
use App\Models\Category;
use App\Models\EverythingPack;
use App\Models\FullCatalogPrices;
use App\Models\Order;
use App\Models\Period;
use App\Services\LectureService;
use App\Services\PaymentService;
use App\Traits\MoneyConversion;
use Illuminate\Http\Response;

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

        $order = Order::create([
            'user_id' => auth()->id(),
            'price' => $price,
            'points' => self::roublesToCoins($refPointsToSpend ?? 0),
            'subscriptionable_type' => EverythingPack::class,
            'subscriptionable_id' => 1,
            'period' => $periodLength,
        ]);

        if ($order) {
            $link = $this->paymentService->createPayment(
                self::coinsToRoubles(
                    $refPointsToSpend ?
                        $price - self::roublesToCoins($refPointsToSpend) :
                        $price
                ),
                ['order_id' => $order->id]
            );

            return response()->json([
                'link' => $link,
            ], Response::HTTP_OK);
        }
    }
}
