<?php

namespace App\Repositories;

use App\Models\Promo;
use Illuminate\Support\Arr;

class PromoRepository
{
    public function getById(int $id): ?Promo
    {
        return Promo::query()
            ->where('id', '=', $id)
            ->first();
    }

    public function getPrices(Promo $promo)
    {
        $periods = $promo->subscriptionPeriodsForPromoPack;
        $prices = [];
        foreach ($periods as $period) {
            $prices[] = [
                'title' => $period->title,
                'length' => $period->length,
                'price' => number_format($period->pivot->price / 100, 2, thousands_separator: ''),
                'price_for_one_lecture' => number_format($period->pivot->price_for_one_lecture / 100, 2, thousands_separator: '')
            ];
        }

        return $prices;
    }

    public function getPriceForExactPeriodLength(Promo $promo, int|string $length): int|float|string
    {
        $allPrices = $this->getPrices($promo);

        $priceForExactPeriod = Arr::where($allPrices, function ($price) use ($length) {
            return $price['length'] == $length;
        });

        $price = Arr::first($priceForExactPeriod)['price'];

        return $price;
    }
}
