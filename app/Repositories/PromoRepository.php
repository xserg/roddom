<?php

namespace App\Repositories;

use App\Models\Lecture;
use App\Models\LecturePaymentType;
use App\Models\Period;
use App\Models\Promo;
use App\Traits\MoneyConversion;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PromoRepository
{
    use MoneyConversion;

    public function __construct(
        private PeriodRepository  $periodRepository,
    ) {
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
            $finPrices = $this->calculatePromoPackPriceForPeriod(1, $period->id);

            $prices[] = [
                'title' => $period->title,
                'length' => $period->length,
                'price_usual' => self::coinsToRoubles($finPrices['usual_price']),
                'price' => self::coinsToRoubles($finPrices['final_price']),
                'price_for_one_lecture' => self::coinsToRoubles($period->pivot->price_for_one_lecture),
            ];
        }

        return $prices;
    }

    public function getCommonPriceForOneLectureForPeriod(int $periodId): int|float|string
    {
        $price = DB::select('SELECT price_for_one_lecture
        FROM promo_pack_prices
        WHERE period_id=?', [$periodId]);

        $price = Arr::first($price);

        return self::coinsToRoubles($price->price_for_one_lecture);
    }

    public function calculatePromoPackPriceForPeriod(int $promoId, int $periodId): array
    {
        $finalPrice = 0;
        $usualPrice = 0;

        /*
         * чтобы дергать лекции у конкретного промопака понадоибся еще одна промежуточная
         * таблица: lecture_id promo_id. Пока дергаем абсолютно все промо лекции, т.к. промопак один
         */

        $relations = [
            'category',
            'category.categoryPrices.period',
            'pricesPeriodsInPromoPacks',
            'pricesForLectures',
        ];

        $promoLectures = Lecture::query()
            ->where('payment_type_id', LecturePaymentType::PROMO)
            ->with($relations)
            ->get();

        if ($promoLectures->isEmpty()) {
            return ['final_price' => $finalPrice, 'usual_price' => $usualPrice];
        }

        foreach ($promoLectures as $promoLecture) {
            // аксессор модели!
            $lecturePrices = $promoLecture->prices;
            $lectureUsualPrices = $this->formPricesForPayedLecture($promoLecture);

            $lecturePriceForPeriod = Arr::where($lecturePrices, function ($value) use ($periodId) {
                return $value['period_id'] == $periodId;
            });
            $lecturePriceForPeriod = Arr::first($lecturePriceForPeriod);

            $lectureUsualPriceForPeriod = Arr::where($lectureUsualPrices, function ($value) use ($periodId) {
                return $value['period_id'] == $periodId;
            });
            $lectureUsualPriceForPeriod = Arr::first($lectureUsualPriceForPeriod);

            $customPrice = $lecturePriceForPeriod['custom_price_for_one_lecture'];
            $commonPrice = $lecturePriceForPeriod['common_price_for_one_lecture'];

            $customUsualPrice = $lectureUsualPriceForPeriod['custom_price_for_one_lecture'];
            $commonUsualPrice = $lectureUsualPriceForPeriod['common_price_for_one_lecture'];

            $finalPrice += $customPrice ?? $commonPrice;
            $usualPrice += $customUsualPrice ?? $commonUsualPrice;
        }

        return ['final_price' => $finalPrice, 'usual_price' => $usualPrice];
    }

    public function formPricesForPayedLecture(Lecture $lecture): array
    {
        $prices = [];

        //если не промо - то не важно, платная или бесплатная,
        //бесплатную тоже можно купить по ценам платной
        //берем общую цену за одну лекцию у категории
        $commonCategoryPrices = $lecture->category->categoryPrices;
        $customPrices = $lecture->pricesForLectures;

        foreach ($commonCategoryPrices as $commonCategoryPrice) {
            $period = $commonCategoryPrice->period;

            //общие цены всегда находятся, по идее тут всегда будет указана цена в priceCommon
            $priceCommon = $commonCategoryPrices->where('period_id', $period->id)->first();
            $priceCustom = $customPrices->where('length', $period->length)->first();

            if (is_null($priceCustom)) {
                $prices[] = [
                    'length' => $period->length,
                    'period_id' => $period->id,
                    'custom_price_for_one_lecture' => null,
                    'common_price_for_one_lecture' => (int)$priceCommon->price_for_one_lecture,
                ];
            } else {
                $priceForOneLecture = $priceCustom->pivot->price;

                $prices[] = [
                    'length' => $period->length,
                    'period_id' => $period->id,
                    'custom_price_for_one_lecture' => (int)$priceForOneLecture,
                    'common_price_for_one_lecture' => (int)$priceCommon->price_for_one_lecture,
                ];
            }
        }

        return $prices;
    }
}
