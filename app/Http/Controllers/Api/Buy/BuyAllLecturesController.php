<?php

namespace App\Http\Controllers\Api\Buy;

use App\Http\Requests\Buy\BuyAllLecturesRequest;
use App\Models\EverythingPack;
use App\Models\Order;
use App\Services\LectureService;
use App\Services\PaymentService;
use App\Services\PurchaseService;
use App\Traits\MoneyConversion;

class BuyAllLecturesController
{
    use MoneyConversion;

    public function __construct(
        private LectureService  $lectureService,
        private PaymentService  $paymentService,
        private PurchaseService $purchaseService
    ) {
    }

    public function __invoke(
        BuyAllLecturesRequest $request,
        int                   $periodLength
    ) {
        $order = $this->createOrder($request, $periodLength);

        $link = $this->paymentService->createPayment(
            self::coinsToRoubles($order->price_to_pay),
            ['order_id' => $order->id]
        );

        return response()->json(['link' => $link]);
    }

    public function prepareOrderForTinkoff(
        BuyAllLecturesRequest $request,
        int                   $periodLength
    ) {
        $order = $this->createOrder($request, $periodLength);

        return response()->json([$order->code]);
    }

    private function createOrder(
        BuyAllLecturesRequest $request,
        int                   $periodLength
    ): Order {
        $dto = $this->lectureService->getEverythingPriceForPeriod($periodLength);
        $refPointsToSpend = self::roublesToCoins($request->validated('ref_points', 0));

        return $this->purchaseService->resolveOrder(
            auth()->id(),
            EverythingPack::class,
            1,
            $dto->getPriceToPay(),
            $dto->getLecturesBoughtCount(),
            $periodLength,
            $refPointsToSpend,
            $dto->getExcluded()
        );
    }
}
