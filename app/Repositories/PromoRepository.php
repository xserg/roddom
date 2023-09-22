<?php

namespace App\Repositories;

use App\Models\Lecture;
use App\Models\LecturePaymentType;
use App\Models\Promo;
use App\Services\LectureService;
use App\Traits\MoneyConversion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PromoRepository
{
    use MoneyConversion;

    public function __construct(
        private LectureService $lectureService
    ) {
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
            $lecturePrices = $this->lectureService->calculatePromoLecturePricesPromoPack($promoLecture);
            $lectureUsualPrices = $this->lectureService->calculateLecturePricesSubCategory($promoLecture);

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

    public function getAllLecturesForPromoPack(int $promoPackId): Collection
    {
        $promoLectures = Lecture::promo()->get();

        if ($promoLectures->isEmpty()) {
            throw new NotFoundHttpException('There are no promo lectures');
        }

        return $promoLectures;
    }

    public function getPromoPackPriceForPeriod(int $promoPackId, int $periodId): int
    {
        $prices = $this->calculatePromoPackPriceForPeriod($promoPackId, $periodId);
        return $prices['final_price'];
    }
}
