<?php

namespace App\Services;

use App\Models\Order;

class PurchaseService
{
    public function resolveOrder(
        int    $userId,
        string $subscriptionableType,
        int    $subscriptionableId,
        int    $price,
        int    $period,
        int    $refPointsToSpend
    ) {
        $priceToPay = $this->calculatePriceToPay($price, $refPointsToSpend);

        if ($this->priceToPayLessThanOneRouble($priceToPay)) {
            $refPointsToSpend = $price - 100;
            $priceToPay = 100;
        }

        return Order::create([
            'user_id' => $userId,
            'price' => $price,
            'price_to_pay' => $priceToPay,
            'points' => $refPointsToSpend,
            'subscriptionable_type' => $subscriptionableType,
            'subscriptionable_id' => $subscriptionableId,
            'period' => $period,
        ]);
    }

    private function priceToPayLessThanOneRouble(int $priceToPay): bool
    {
        return $priceToPay < 100;
    }

    private function calculatePriceToPay(int $price, int $refPointsToSpend): int
    {
        return $refPointsToSpend > 0 ?
            $price - $refPointsToSpend :
            $price;
    }
}
