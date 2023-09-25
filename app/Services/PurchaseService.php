<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class PurchaseService
{
    /**
     * @param array $exclude Массив с айди лекций, которые при покупке нужно будет исключить из доступных
     */
    public function resolveOrder(
        int                                 $userId,
        string                              $subscriptionableType,
        int                                 $subscriptionableId,
        int                                 $priceWithDiscounts,
        int                                 $lecturesBoughtCount,
        int                                 $period,
        int                                 $refPointsToSpend,
        array|Collection|EloquentCollection $exclude = []
    ): Order {
        $priceToPay = $this->calculatePriceToPay($priceWithDiscounts, $refPointsToSpend);

        if ($this->priceToPayLessThanOneRouble($priceToPay)) {
            $refPointsToSpend = $priceWithDiscounts - 100;
            $priceToPay = 100;
        }

        return Order::create([
            'user_id' => $userId,
            'price' => $priceWithDiscounts,
            'price_to_pay' => $priceToPay,
            'points' => $refPointsToSpend,
            'subscriptionable_type' => $subscriptionableType,
            'subscriptionable_id' => $subscriptionableId,
            'lectures_count' => $lecturesBoughtCount,
            'period' => $period,
            'exclude' => $exclude
        ]);
    }

    private
    function priceToPayLessThanOneRouble(
        int $priceToPay
    ): bool {
        return $priceToPay < 100;
    }

    private
    function calculatePriceToPay(
        int $price, int $refPointsToSpend
    ): int {
        return $refPointsToSpend > 0 ?
            $price - $refPointsToSpend :
            $price;
    }
}
