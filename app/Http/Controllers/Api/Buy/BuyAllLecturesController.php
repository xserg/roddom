<?php

namespace App\Http\Controllers\Api\Buy;

use App\Http\Requests\Buy\BuyAllLecturesRequest;
use App\Models\Category;
use App\Models\EverythingPack;
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
        $mainCategories = Category::mainCategories()->with([
            'childrenCategories.categoryPrices.period',
            'childrenCategories.parentCategory',
            'childrenCategories.categoryPrices',
            'childrenCategories.lectures.category.categoryPrices',
            'childrenCategories.lectures.pricesInPromoPacks',
            'childrenCategories.lectures.pricesForLectures',
            'childrenCategories.lectures.pricesPeriodsInPromoPacks',
            'childrenCategories.lectures.paymentType',
            'childrenCategories.lectures.contentType',
        ])->get();

        $price = self::coinsToRoubles(
            $this->lectureService->calculateEverythingPriceByPeriod($mainCategories, $period->id)
        );

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
