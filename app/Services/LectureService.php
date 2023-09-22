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
        $purchasedLecturesIds = $this->lectureRepository->getAllPurchasedLectureIdsForCurrentUser();

        return in_array($lectureId, $purchasedLecturesIds);
    }

    public function formAllLecturePrices(): array
    {
        $prices = [];

        $fullCatalogPrices = FullCatalogPrices::with('period')->get();
        $lecturesCount = Lecture::count();

        foreach ($this->periods as $period) {
            $fullCatalogPricesForPeriod = $fullCatalogPrices->where('period_id', $period->id)->first();

            $prices[] = [
                'lectures_count' => $lecturesCount,
                'period_id' => $period->id,
                'period_length' => $period->length,
                'price_for_catalog' => self::coinsToRoubles(
                    $this->calculateEverythingPriceByPeriod($fullCatalogPricesForPeriod)
                ),
                'price_for_catalog_promo' => self::coinsToRoubles(
                    $this->calculateEverythingPricePromoByPeriod($fullCatalogPricesForPeriod)
                ),
                'is_promo' => $fullCatalogPricesForPeriod->is_promo
            ];
        }

        return $prices;
    }

    public function calculateEverythingPriceByPeriod(FullCatalogPrices $fullCatalogPrices): int
    {
        $price = 0;

        $lecturesCount = Lecture::payed()->count();
        $price += ($lecturesCount * $fullCatalogPrices->price_for_one_lecture);

        return $price;
    }

    public function calculateEverythingPricePromoByPeriod(FullCatalogPrices $fullCatalogPrices): int
    {
        $price = 0;

        $lecturesCount = Lecture::payed()->count();
        $price += ($lecturesCount * $fullCatalogPrices->price_for_one_lecture_promo);

        return $price;
    }

    public function getLecturePriceForPeriod(int $lectureId, int $periodLength): int
    {
        $lecture = $this->lectureRepository->getLectureById($lectureId);

        if ($lecture->isFree()) {
            return 0;
        }

        $prices = $this->calculatePrices($lecture);

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

    public function calculatePromoLecturePricesPromoPack(Lecture|LectureResource $lecture): array
    {
        $prices = [];

        $promoCustomPrices = $lecture->pricesPeriodsInPromoPacks;
        $promoCommonPrices = $this->promoCommonPrices;

        foreach ($this->periods as $period) {
            //общие цены всегда находятся, по идее в priceCommon всегда будет указана цена
            $priceCommon = $promoCommonPrices->where('length', $period->length)->first();
            $priceCustom = $promoCustomPrices->where('length', $period->length)->first();

            $priceCommonForOneLecture = (int) $priceCommon->pivot->price_for_one_lecture;

            //а вот кастомной цены может не быть, поэтому проверяем
            if (is_null($priceCustom)) {
                //если нет кастомной цены для конкретного периода, ставим null
                $prices[] = [
                    'length' => $period->length,
                    'period_id' => $period->id,
                    'custom_price_for_one_lecture' => null,
                    'common_price_for_one_lecture' => $priceCommonForOneLecture,
                ];
            } else {
                //common_price_for_one_lecture одна и таже в обоих случаях
                $priceForOneLecture = (int) $priceCustom->pivot->price;

                $prices[] = [
                    'length' => $period->length,
                    'period_id' => $period->id,
                    'custom_price_for_one_lecture' => $priceForOneLecture,
                    'common_price_for_one_lecture' => $priceCommonForOneLecture,
                ];
            }
        }

        return $prices;
    }

    public function calculateLecturePricesSubCategory(Lecture|LectureResource $lecture): array
    {
        $prices = [];

        //берем общую цену за одну лекцию у категории
        $commonCategoryPrices = $lecture->category->categoryPrices;
        $customPrices = $lecture->pricesForLectures;

        foreach ($this->periods as $period) {
            //общие цены всегда находятся, по идее тут всегда будет указана цена в priceCommon
            $priceCommon = $commonCategoryPrices->where('period_id', $period->id)->first();
            $priceCustom = $customPrices->where('length', $period->length)->first();

            if (is_null($priceCustom)) {
                $prices[] = [
                    'length' => $period->length,
                    'period_id' => $period->id,
                    'custom_price_for_one_lecture' => null,
                    'common_price_for_one_lecture' => (int) $priceCommon->price_for_one_lecture,
                ];
            } else {
                $priceForOneLecture = $priceCustom->pivot->price;

                $prices[] = [
                    'length' => $period->length,
                    'period_id' => $period->id,
                    'custom_price_for_one_lecture' => (int) $priceForOneLecture,
                    'common_price_for_one_lecture' => (int) $priceCommon->price_for_one_lecture,
                ];
            }
        }

        return $prices;
    }

    public function formPricesPromoLectureSubCategory(Lecture $lecture): array
    {
        $prices = [];

        // всегда будут установлены общие цены в рамках сабкатегории
        $subCategoryCommonPrices = $lecture->category->categoryPrices;

        // кастомные цены в рамках лекции
        $lectureCustomPrices = $lecture->pricesForLectures;

        foreach ($this->periods as $period) {
            //общие цены всегда находятся, по идее в priceCommon всегда будет указана цена
            $priceCommon = $subCategoryCommonPrices->where('period_id', $period->id)->first();
            $priceCustom = $lectureCustomPrices->where('period_id', $period->id)->first();
            $priceCommonForOneLecture = $priceCommon->price_for_one_lecture_promo;

            //а вот кастомной цены может не быть, поэтому проверяем
            if (is_null($priceCustom)) {
                //если нет кастомной цены для конкретного периода, ставим null
                $customPriceForOneLecture = null;
            } else {
                $customPriceForOneLecture = (int) $priceCustom->pivot->price;
            }

            $prices[] = [
                'length' => $period->length,
                'period_id' => $period->id,
                'custom_price_for_one_lecture' => $customPriceForOneLecture,
                'common_price_for_one_lecture' => $priceCommonForOneLecture,
            ];
        }

        return $prices;
    }

    public function formLecturePricesMainCategory(Lecture|LectureResource $lecture): array
    {
        $prices = [];

        //берем общую цену за одну лекцию у категории
        $commonCategoryPrices = $lecture->category->parentCategory->categoryPrices;
        $customPrices = $lecture->pricesForLectures;

        foreach ($this->periods as $period) {
            //общие цены всегда находятся, по идее тут всегда будет указана цена в priceCommon
            $priceCommon = $commonCategoryPrices->where('period_id', $period->id)->first();
            $priceCustom = $customPrices->where('length', $period->length)->first();

            if (is_null($priceCustom)) {
                $prices[] = [
                    'length' => $period->length,
                    'period_id' => $period->id,
                    'custom_price_for_one_lecture' => null,
                    'common_price_for_one_lecture' => (int) $priceCommon->price_for_one_lecture,
                ];
            } else {
                $priceForOneLecture = $priceCustom->pivot->price;

                $prices[] = [
                    'length' => $period->length,
                    'period_id' => $period->id,
                    'custom_price_for_one_lecture' => (int) $priceForOneLecture,
                    'common_price_for_one_lecture' => (int) $priceCommon->price_for_one_lecture,
                ];
            }
        }

        return $prices;
    }

    public function formPromoLecturePricesMainCategory(Lecture $lecture): array
    {
        $prices = [];

        // всегда будут установлены общие цены в рамках сабкатегории
        $subCategoryCommonPrices = $lecture->category->parentCategory->categoryPrices;

        // кастомные цены в рамках лекции
        $lectureCustomPrices = $lecture->pricesForLectures;

        foreach ($this->periods as $period) {
            //общие цены всегда находятся, по идее в priceCommon всегда будет указана цена
            $priceCommon = $subCategoryCommonPrices->where('period_id', $period->id)->first();
            $priceCustom = $lectureCustomPrices->where('period_id', $period->id)->first();
            $priceCommonForOneLecture = $priceCommon->price_for_one_lecture_promo;

            //а вот кастомной цены может не быть, поэтому проверяем
            if (is_null($priceCustom)) {
                //если нет кастомной цены для конкретного периода, ставим null
                $customPriceForOneLecture = null;
            } else {
                $customPriceForOneLecture = (int) $priceCustom->pivot->price;
            }

            $prices[] = [
                'length' => $period->length,
                'period_id' => $period->id,
                'custom_price_for_one_lecture' => $customPriceForOneLecture,
                'common_price_for_one_lecture' => $priceCommonForOneLecture,
            ];
        }

        return $prices;
    }

    private function calculatePrices(Lecture $lecture): array
    {
        if ($lecture->isPromo()) {
            return $this->calculatePromoLecturePricesPromoPack($lecture);
        }

        return $this->calculateLecturePricesSubCategory($lecture);
    }

    public function getEverythingPriceForPeriod(int $periodLength): int
    {
        $period = $this->periods->where('length', $periodLength);
        $fullCatalogPrices = FullCatalogPrices::with('period')->get();
        $fullCatalogPricesForPeriod = $fullCatalogPrices->firstWhere('period_id', $period->id);

        if ($fullCatalogPricesForPeriod->is_promo) {
            return $this->calculateEverythingPricePromoByPeriod($fullCatalogPricesForPeriod);
        }

        return $this->calculateEverythingPriceByPeriod($fullCatalogPricesForPeriod);
    }
}
