<?php

namespace App\Services;

use App\Models\Category;
use App\Models\EverythingPack;
use App\Models\Lecture;
use App\Models\Order;
use App\Models\Promo;
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

    public function resolveEntityTitle(string $subscriptionable_type, int $subscriptionable_id): string
    {
        if ($subscriptionable_type === Lecture::class) {
            return 'Лекция: ' . Lecture::query()->find($subscriptionable_id)->title;
        } elseif ($subscriptionable_type === Category::class) {
            return 'Категория: ' . Category::query()->find($subscriptionable_id)->title;
        } elseif ($subscriptionable_type === Promo::class) {
            return 'Промопак лекций';
        } elseif ($subscriptionable_type === EverythingPack::class) {
            return 'Все лекции';
        } else {
            return 'Заголовок лекции не определён';
        }
    }
}
