<?php

namespace App\Services;

use App\Http\Resources\LectureResource;
use App\Models\Category;
use App\Models\FullCatalogPrices;
use App\Models\Lecture;
use App\Models\Period;
use App\Models\Promo;
use App\Repositories\CategoryRepository;
use App\Repositories\LectureRepository;
use App\Repositories\UserRepository;
use App\Traits\MoneyConversion;
use Illuminate\Support\Arr;

class LectureService
{
    use MoneyConversion;

    private $periods;
    private $promoCommonPrices;

    public function __construct(
        private LectureRepository  $lectureRepository,
        private UserRepository     $userRepository,
        private CategoryRepository $categoryRepository,
        private CategoryService    $categoryService,
    ) {
        $this->periods = Period::all();
        $this->promoCommonPrices = Promo::query()
            ->with(['subscriptionPeriodsForPromoPack'])
            ->first()
            ->subscriptionPeriodsForPromoPack;
    }

    public function isLecturePurchased(int $lectureId): bool
    {
        $purchasedLecturesIds = $this->lectureRepository->getPurchasedLectures();

        return $purchasedLecturesIds->contains('id', $lectureId);
    }

    public function formAllLecturePrices(): array
    {
        $prices = [];

        $fullCatalogPrices = FullCatalogPrices::with('period')->get();
        $lecturesCount = Lecture::count();

        foreach ($this->periods as $period) {
            $fullCatalogPricesForPeriod = $fullCatalogPrices->firstWhere('period_id', $period->id);

            $prices[] = [
                'lectures_count' => $lecturesCount,
                'period_id' => $period->id,
                'period_length' => $period->length,
                'price_for_catalog' => self::coinsToRoubles(
                    $this->calculateEverythingPriceForPeriod($fullCatalogPricesForPeriod)
                ),
                'price_for_catalog_promo' => self::coinsToRoubles(
                    $this->calculateEverythingPriceForPeriod($fullCatalogPricesForPeriod, true)
                ),
                'is_promo' => $fullCatalogPricesForPeriod->is_promo
            ];
        }

        return $prices;
    }

    public function calculateEverythingPriceForPeriod(FullCatalogPrices $fullCatalogPrices, bool $isPromo = false): int
    {
        $price = 0;

        $priceForOneLecture = $isPromo
            ? $fullCatalogPrices->price_for_one_lecture_promo
            : $fullCatalogPrices->price_for_one_lecture;

        $lecturesCount = Lecture::payed()->count();
        $price += ($lecturesCount * $priceForOneLecture);

        return $price;
    }

    public function getLecturePriceForPeriod(int $lectureId, int $periodLength): int
    {
        $lecture = $this->lectureRepository->getLectureById($lectureId);

        if ($lecture->isFree()) {
            return 0;
        }

        $prices = $this->getPrices($lecture);

        $priceArr = Arr::where(
            $prices,
            fn ($value) => $value['length'] == $periodLength
        );
        $priceArr = Arr::first($priceArr);

        $price =
            $priceArr['custom_price_for_one_lecture'] ??
            $priceArr['common_price_for_one_lecture'];

        return $price;
    }

    public function getLecturePricesInCasePromoPack(Lecture|LectureResource $lecture): array
    {
        $prices = [];

        $commonPromoPrices = $this->promoCommonPrices;
        $customPromoPrices = $lecture->pricesPeriodsInPromoPacks;

        foreach ($this->periods as $period) {
            //общие цены всегда находятся, по идее в commonPrice всегда будет указана цена
            $commonPrice = $commonPromoPrices->firstWhere('length', $period->length);
            $customPrice = $customPromoPrices->firstWhere('length', $period->length);

            $commonPriceForOneLecture = (int) $commonPrice->pivot->price_for_one_lecture;
            $customPriceForOneLecture = $customPrice?->pivot?->price;

            $prices[] = [
                'length' => $period->length,
                'period_id' => $period->id,
                'custom_price_for_one_lecture' => $customPriceForOneLecture,
                'common_price_for_one_lecture' => $commonPriceForOneLecture,
            ];
        }

        return $prices;
    }

    private function getPrices(Lecture $lecture): array
    {
        if ($lecture->isPromo()) {
            return $this->getLecturePricesInCasePromoPack($lecture);
        }

        return $this->categoryService->getLecturePricesInCaseSubCategory($lecture);
    }

    public function getEverythingPriceForPeriod(int $periodLength): int
    {
        $period = $this->periods->firstWhere('length', $periodLength);
        $fullCatalogPrices = FullCatalogPrices::with('period')->get();
        $fullCatalogPricesForPeriod = $fullCatalogPrices->firstWhere('period_id', $period->id);

        $isPromo = $fullCatalogPricesForPeriod->is_promo;

        return $this->calculateEverythingPriceForPeriod($fullCatalogPricesForPeriod, $isPromo);
    }
}
