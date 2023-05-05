<?php

namespace App\Repositories;

use App\Models\Lecture;
use App\Models\LecturePaymentType;
use App\Models\Promo;
use App\Traits\MoneyConversion;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PromoRepository
{
    use MoneyConversion;

    public function __construct(
        private PeriodRepository $periodRepository
    )
    {
    }

    public function getById(int $id): ?Promo
    {
        return Promo::query()
            ->where('id', '=', $id)
            ->first();
    }

    public function getPrices(Promo $promo): array
    {
        $periods = $promo->subscriptionPeriodsForPromoPack;
        $prices = [];
        foreach ($periods as $period) {
            $prices[] = [
                'title' => $period->title,
                'length' => $period->length,
                'price' => $this->getPriceForPackForPeriod(1, $period->id),
                'price_for_one_lecture' => number_format($period->pivot->price_for_one_lecture / 100, 2, thousands_separator: '')
            ];
        }

        return $prices;
    }

    public function getCommonPriceForOneLectureForPeriod(int $periodId): int|float|string
    {
        $price = DB::select('SELECT price_for_one_lecture
        FROM `mothers-school`.promo_pack_prices
        WHERE period_id=?', [$periodId]);

        $price = Arr::first($price);

        return self::coinsToRoubles($price->price_for_one_lecture);
    }

    public function getPriceForPackForPeriod(int $promoId, int $periodId): int|float|string
    {
        $finalPrice = 0;

        /*
         * чтобы дергать лекции у конкретного промопака понадоибся еще одна промежуточная
         * таблица: lecture_id promo_id. Пока дергаем абсолютно все промо лекции, т.к. промопак один
         */
        $promoLectures = Lecture::query()
            ->where('payment_type_id', LecturePaymentType::PROMO)
            ->get();

        if ($promoLectures->isEmpty()) {
            return $finalPrice;
        };

        foreach ($promoLectures as $promoLecture) {
            $lecturePrices = $promoLecture->prices;

            $lecturePriceForPeriod = Arr::where($lecturePrices, function ($value) use ($periodId) {
                return $value['period_id'] == $periodId;
            });
            $lecturePriceForPeriod = Arr::first($lecturePriceForPeriod);

            $customPrice = $lecturePriceForPeriod['custom_price_for_one_lecture'];
            $commonPrice = $lecturePriceForPeriod['common_price_for_one_lecture'];

            $finalPrice += $customPrice ?? $commonPrice;
        }

        return round($finalPrice, 2);
    }
}
