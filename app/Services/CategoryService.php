<?php

namespace App\Services;

use App\Dto\CategoryPurchaseDto;
use App\Http\Resources\LectureResource;
use App\Models\Category;
use App\Models\Lecture;
use App\Models\Period;
use App\Repositories\CategoryRepository;
use App\Repositories\LectureRepository;
use App\Repositories\UserRepository;
use App\Traits\MoneyConversion;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class CategoryService
{
    use MoneyConversion;

    private EloquentCollection $periods;

    public function __construct(
        private UserRepository     $userRepository,
        private CategoryRepository $categoryRepository,
        private LectureRepository  $lectureRepository,
        private PaymentService     $paymentService,
    ) {
        $this->periods = Period::all();
    }

    public function isCategoryPurchased(int $categoryId): bool
    {
        $categorySubscriptions = $this->userRepository
            ->categorySubscriptions();

        if (
            is_null($categorySubscriptions)
            || $categorySubscriptions->isEmpty()
        ) {
            return false;
        }

        $categoriesSubscriptions = $categorySubscriptions
            ->where('subscriptionable_id', $categoryId);

        foreach ($categoriesSubscriptions as $subscription) {
            if ($subscription->isActual()) {
                return true;
            }
        }

        return false;
    }

    public function areAllCategoryLecturesPurchased(int $categoryId, ?int $userId = null): bool
    {
        $category = $this->categoryRepository->getCategoryById($categoryId);
        $purchasedLectures = $this->lectureRepository->getPurchasedLectures($userId);

        if ($category->isSub()) {
            $intersect = $category->lectures->intersect($purchasedLectures);
            if ($intersect->count() === $category->lectures->count()) {
                return true;
            }
        } else {
            $allSubCategoryLectures = $category->childrenCategories->map(fn ($subCategory) => $subCategory->lectures)->flatten();
            $intersect = $allSubCategoryLectures->intersect($purchasedLectures);
            if ($intersect->count() === $allSubCategoryLectures->count()) {
                return true;
            }
        }

        return false;
    }

    public function getCategoryPricesResource($category, ?int $userId = null): array
    {
        $categoryPricesResource = [];

        foreach ($this->periods as $period) {
            $categoryPriceDto = $category->isMain()
                ? $this->calculateMainCategoryPriceForPeriod($category, $period->id, $userId)
                : $this->calculateSubCategoryPriceForPeriod($category, $period->id, $userId);

            $categoryPricesResource[] = $this->getCategoryPriceResourceForPeriod($categoryPriceDto, $period);
        }

        return $categoryPricesResource;
    }

    public function getCategoryPurchaseForPeriod(int $categoryId, int $periodId, ?int $userId = null): CategoryPurchaseDto
    {
        $relations = [
            'childrenCategoriesLectures.category.parentCategory.categoryPrices',
            'childrenCategories.lectures.category.parentCategory.categoryPrices',
            'childrenCategoriesLectures.pricesForLectures',
            'childrenCategories.categoryPrices.period',
            'childrenCategories.parentCategory',
            'childrenCategories.categoryPrices',
            'childrenCategories.lectures.category.categoryPrices',
            'childrenCategories.lectures.pricesInPromoPacks',
            'childrenCategories.lectures.pricesForLectures',
            'childrenCategories.lectures.pricesPeriodsInPromoPacks',
            'childrenCategories.lectures.paymentType',
            'childrenCategories.lectures.contentType',
        ];
        $category = $this->categoryRepository->getCategoryById($categoryId, $relations);
        return $this->calculateAnyCategoryTypePriceForPeriod($category, $periodId, $userId);
    }

    private function calculateAnyCategoryTypePriceForPeriod(?Category $category, int $periodId, ?int $userId = null): CategoryPurchaseDto
    {
        if ($category->isSub()) {
            return $this->calculateSubCategoryPriceForPeriod($category, $periodId, $userId);
        }

        return $this->calculateMainCategoryPriceForPeriod($category, $periodId, $userId);
    }

    public function calculateSubCategoryPriceForPeriod(
        $category,
        int $periodId,
        ?int $userId = null
    ): CategoryPurchaseDto {
        $price = 0;
        $pricePromo = 0;

        foreach ($category->lectures as $lecture) {
            if ($lecture->isFree()) {
                return new CategoryPurchaseDto($category);
            }

            $lecturePrices = $this->getLecturePricesInCaseSubCategory($lecture);
            $lecturePromoPrices = $this->getLecturePricesInCaseSubCategory($lecture, true);

            $this->addToPrices($lecturePrices, $lecturePromoPrices, $price, $pricePromo, $periodId);
        }

        return $this->resolveCategoryPurchase(
            $price,
            $pricePromo,
            $category,
            $category->lectures,
            $userId
        );
    }

    public function calculateMainCategoryPriceForPeriod(
        $category,
        int $periodId,
        ?int $userId = null
    ): CategoryPurchaseDto {
        $price = 0;
        $pricePromo = 0;

        $lecturesCount = $category->children_categories_lectures_count;

        if ($lecturesCount === 0) {
            return new CategoryPurchaseDto(
                $category
            );
        }

        $allSubCategoryLectures = $category->childrenCategories->map(fn (Category $category) => $category->lectures)->flatten();

        foreach ($allSubCategoryLectures as $lecture) {
            if ($lecture->isFree()) {
                return new CategoryPurchaseDto($category);
            }

            $lecturePrices = $this->getLecturePricesInCaseMainCategory($lecture);
            $lecturePromoPrices = $this->getLecturePricesInCaseMainCategory($lecture, true);

            $this->addToPrices($lecturePrices, $lecturePromoPrices, $price, $pricePromo, $periodId);
        }

        return $this->resolveCategoryPurchase(
            $price,
            $pricePromo,
            $category,
            $allSubCategoryLectures,
            $userId
        );
    }

    private function resolveCategoryPurchase(
        int        $initialPrice,
        int        $initialPricePromo,
                   $categoryToPurchase,
        Collection $lecturesToPurchase,
        ?int       $userId = null,
    ): CategoryPurchaseDto {
        $purchasedLectures = $this->lectureRepository->getPurchasedLectures($userId);

        $decreased = $this->paymentService->resolveDiscounts(
            $purchasedLectures,
            $lecturesToPurchase,
            $initialPrice,
            $initialPricePromo);

        $priceToPay = $initialPrice - $decreased->getDecreasedCurrency() ?: 0;
        $priceToPayPromo = $initialPricePromo - $decreased->getDecreasedCurrencyPromo() ?: 0;

        return new CategoryPurchaseDto(
            $categoryToPurchase,
            $initialPrice,
            $initialPricePromo,
            $priceToPay,
            $priceToPayPromo,
            $decreased->getStatus(),
            $decreased->getDecreasedPercent(),
            $decreased->getDecreasedCount(),
            $decreased->getDecreasedCurrency(),
            $decreased->getDecreasedCurrencyPromo(),
            $decreased->getExcluded()
        );
    }

    public function getLecturePricesInCaseSubCategory(Lecture|LectureResource $lecture, bool $isPromo = false): array
    {
        //берем общую цену за одну лекцию у категории
        $commonCategoryPrices = $lecture->category->categoryPrices;
        $customPrices = $lecture->pricesForLectures;

        return $this->resolvePrices($commonCategoryPrices, $customPrices, $isPromo);
    }

    public function getLecturePricesInCaseMainCategory(Lecture|LectureResource $lecture, bool $isPromo = false): array
    {
        //берем общую цену за одну лекцию у категории
        $commonCategoryPrices = $lecture->category->parentCategory->categoryPrices;
        $customPrices = $lecture->pricesForLectures;

        return $this->resolvePrices($commonCategoryPrices, $customPrices, $isPromo);
    }

    /**
     * @param EloquentCollection $categoryCommonPrices
     * @param EloquentCollection $lectureCustomPrices
     * @param bool $isPromo
     * @return array Массив вида
     * [
     *  [
     *      'period_id' => $period->id,
     *      'length' => $period->length,
     *      'custom_price_for_one_lecture' => (int) $priceForOneLecture,
     *      'common_price_for_one_lecture' => (int) $priceCommon->price_for_one_lecture,
     *  ], ...]
     */
    private function resolvePrices(
        EloquentCollection $categoryCommonPrices,
        EloquentCollection $lectureCustomPrices,
        bool               $isPromo = false
    ): array {
        $prices = [];

        foreach ($this->periods as $period) {
            $priceCommon = $categoryCommonPrices->firstWhere('period_id', $period->id);
            $priceCustom = $lectureCustomPrices->firstWhere('period_id', $period->id);

            //общие цены всегда находятся, по идее в priceCommon всегда будет указана цена
            $commonPriceForOneLecture =
                $isPromo
                    ? (int) $priceCommon->price_for_one_lecture_promo
                    : (int) $priceCommon->price_for_one_lecture;

            //а вот кастомной цены может не быть, поэтому проверяем
            $customPriceForOneLecture = $priceCustom?->pivot?->price;

            $prices[] = [
                'length' => $period->length,
                'period_id' => $period->id,
                'custom_price_for_one_lecture' => $customPriceForOneLecture,
                'common_price_for_one_lecture' => $commonPriceForOneLecture,
            ];
        }

        return $prices;
    }

    private function addToPrices(array $lecturePrices, array $lecturePromoPrices, &$price, &$pricePromo, $periodId): void
    {
        // not promo
        $lecturePriceForPeriod = Arr::where($lecturePrices, function ($value) use ($periodId) {
            return $value['period_id'] == $periodId;
        });
        $lecturePriceForPeriod = Arr::first($lecturePriceForPeriod);

        $customPrice = $lecturePriceForPeriod['custom_price_for_one_lecture'];
        $commonPrice = $lecturePriceForPeriod['common_price_for_one_lecture'];

        $price += $customPrice ?? $commonPrice;

        // promo
        $lecturePromoPriceForPeriod = Arr::where($lecturePromoPrices, function ($value) use ($periodId) {
            return $value['period_id'] == $periodId;
        });
        $lecturePromoPriceForPeriod = Arr::first($lecturePromoPriceForPeriod);

        $customPromoPrice = $lecturePromoPriceForPeriod['custom_price_for_one_lecture'];
        $commonPromoPrice = $lecturePromoPriceForPeriod['common_price_for_one_lecture'];

        $pricePromo += $customPromoPrice ?? $commonPromoPrice;
    }

    private function getCategoryPriceResourceForPeriod(CategoryPurchaseDto $categoryPriceDto, $period): array
    {
        return [
            'title' => $period->title,
            'length' => $period->length,
            'price_for_category' => self::coinsToRoubles($categoryPriceDto->getUsualPriceToPay()),
            'price_for_category_promo' => self::coinsToRoubles($categoryPriceDto->getPromoPriceToPay()),
            'initial_price_for_category' => self::coinsToRoubles($categoryPriceDto->getInitialUsualPrice()),
            'initial_price_for_category_promo' => self::coinsToRoubles($categoryPriceDto->getInitialPromoPrice()),
            'discount' => [
                'status' => $categoryPriceDto->isDiscounted(),
                'percent' => $categoryPriceDto->getPercent(),
                'already_purchased_count' => $categoryPriceDto->getIntersectCount(),
                'discount_on' => self::coinsToRoubles($categoryPriceDto->getDiscountOn()),
                'discount_on_promo' => self::coinsToRoubles($categoryPriceDto->getDiscountOnPromo())
            ]
        ];
    }
}
