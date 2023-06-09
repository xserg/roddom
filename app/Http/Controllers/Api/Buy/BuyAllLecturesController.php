<?php

namespace App\Http\Controllers\Api\Buy;

use App\Models\EverythingPack;
use App\Models\Order;
use App\Services\LectureService;
use App\Services\PaymentService;
use Illuminate\Http\Response;

class BuyAllLecturesController
{
    public function __construct(
        private LectureService $lectureService,
        private PaymentService     $paymentService
    ) {
    }

    public function __invoke(
        int $periodLength
    )
    {
        $price = $this->lectureService->calculateEverythingPriceByPeriod();

        $order = Order::create([
            'user_id' => auth()->id(),
            'price' => $price,
            'subscriptionable_type' => EverythingPack::class,
            'subscriptionable_id' => 1,
            'period' => $periodLength,
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
