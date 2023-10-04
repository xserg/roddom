<?php

namespace App\Services;

use App\Dto\CategoryPurchaseDto;
use App\Dto\EverythingPackPurchaseDto;
use App\Http\Resources\LectureResource;
use App\Models\FullCatalogPrices;
use App\Models\Lecture;
use App\Models\Period;
use App\Models\Promo;
use App\Repositories\CategoryRepository;
use App\Repositories\LectureRepository;
use App\Repositories\UserRepository;
use App\Traits\MoneyConversion;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

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
        private PaymentService     $paymentService
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

    public function getEverythingPackPricesResource(?int $userId = null): array
    {
        $prices = [];

        $fullCatalogPrices = FullCatalogPrices::with('period')->get();

        foreach ($this->periods as $period) {
            $categoryPriceDto = $this->calculateEverythingPriceForPeriod($fullCatalogPrices, $period->id, $userId);
            $prices[] = $this->getEverythingPackPriceResourceForPeriod($categoryPriceDto, $period);
        }

        return $prices;
    }

    public function calculateEverythingPriceForPeriod(EloquentCollection $fullCatalogPrices, int $periodId, ?int $userId = null): EverythingPackPurchaseDto
    {
        /** @var FullCatalogPrices $fullCatalogPricesForPeriod */
        $fullCatalogPricesForPeriod = $fullCatalogPrices->firstWhere('period_id', $periodId);

        $priceForOneLecture = $fullCatalogPricesForPeriod->price_for_one_lecture;
        $priceForOneLecturePromo = $fullCatalogPricesForPeriod->price_for_one_lecture_promo;

        $lecturesToPurchase = Lecture::all();
        $lecturesCount = $lecturesToPurchase->count();
        $initialPrice = $lecturesCount * $priceForOneLecture;
        $initialPricePromo = $lecturesCount * $priceForOneLecturePromo;

        return $this->resolveEverythingPackPurchaseDto(
            $initialPrice,
            $initialPricePromo,
            $fullCatalogPricesForPeriod,
            $lecturesToPurchase,
            $userId
        );
    }

    private function resolveEverythingPackPurchaseDto(
        int               $initialPrice,
        int               $initialPricePromo,
        FullCatalogPrices $fullCatalogPricesForPeriod,
        Collection        $lecturesToPurchase,
        ?int              $userId = null,
    ): EverythingPackPurchaseDto {
        $purchasedLectures = $this->lectureRepository->getPurchasedLectures($userId);

        $discountsDto = $this->paymentService->resolveDiscounts(
            $purchasedLectures,
            $lecturesToPurchase,
            $initialPrice,
            $initialPricePromo
        );

        $priceToPay = $initialPrice - $discountsDto->getDiscountedCurrency() ?: 0;
        $priceToPayPromo = $initialPricePromo - $discountsDto->getDiscountedCurrencyPromo() ?: 0;

        return new EverythingPackPurchaseDto(
            $fullCatalogPricesForPeriod->isPromo(),
            $lecturesToPurchase->count() - $discountsDto->getExcludedCount(),
            $initialPrice,
            $initialPricePromo,
            $priceToPay,
            $priceToPayPromo,
            $discountsDto->getStatus(),
            $discountsDto->getExcludedPercent(),
            $discountsDto->getExcludedCount(),
            $discountsDto->getDiscountedCurrency(),
            $discountsDto->getDiscountedCurrencyPromo(),
            $discountsDto->getExcluded()
        );
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

    public function getEverythingPriceForPeriod(int $periodLength, ?int $userId = null): EverythingPackPurchaseDto
    {
        $period = $this->periods->firstWhere('length', $periodLength);
        $fullCatalogPrices = FullCatalogPrices::with('period')->get();

        return $this->calculateEverythingPriceForPeriod($fullCatalogPrices, $period->id, $userId);
    }

    private function getEverythingPackPriceResourceForPeriod(EverythingPackPurchaseDto $everythingPackPurchaseDto, Period $period): array
    {
        $lecturesCount = Lecture::count();

        return [
            'lectures_count' => $lecturesCount,
            'period_id' => $period->id,
            'period_length' => $period->length,
            'is_promo' => $everythingPackPurchaseDto->isPromo(),
            'price_for_catalog' => self::coinsToRoubles($everythingPackPurchaseDto->getUsualPriceToPay()),
            'price_for_catalog_promo' => self::coinsToRoubles($everythingPackPurchaseDto->getPromoPriceToPay()),
            'initial_price_for_catalog' => self::coinsToRoubles($everythingPackPurchaseDto->getInitialUsualPrice()),
            'initial_price_for_catalog_promo' => self::coinsToRoubles($everythingPackPurchaseDto->getInitialPromoPrice()),
            'discount' => [
                'status' => $everythingPackPurchaseDto->isDiscounted(),
                'percent' => $everythingPackPurchaseDto->getIntersectPercent(),
                'already_purchased_count' => $everythingPackPurchaseDto->getIntersectCount(),
                'discount_on' => self::coinsToRoubles($everythingPackPurchaseDto->getDiscountOn()),
                'discount_on_promo' => self::coinsToRoubles($everythingPackPurchaseDto->getDiscountOnPromo())
            ]
        ];
    }
}
