<?php

namespace App\Services;

use App\Dto\CategoryPricesDto;
use App\Models\Category;
use App\Models\Period;
use App\Repositories\CategoryRepository;
use App\Repositories\UserRepository;
use App\Traits\MoneyConversion;
use Illuminate\Support\Arr;

class CategoryService
{
    use MoneyConversion;

    public function __construct(
        private UserRepository     $userRepository,
        private CategoryRepository $categoryRepository
    ) {
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

    public function formMainCategoryPrices(Category $category): array
    {
        $result = [];

        foreach (Period::all() as $period) {
            $categoryPriceDto = $this->calculateMainCategoryPriceForPeriod($category, $period->id);

            $categoryPriceInRoubles = self::coinsToRoubles($categoryPriceDto->getUsualPrice());
            $categoryPricePromoInRoubles = self::coinsToRoubles($categoryPriceDto->getPromoPrice());

            $result[] = [
                'title' => $period->title,
                'length' => $period->length,
                'price_for_category' => $categoryPriceInRoubles,
                'price_for_category_promo' => $categoryPricePromoInRoubles,
            ];
        }

        return $result;
    }

    public function formSubCategoryPrices(Category $category): array
    {
        $prices = $category->categoryPrices;
        $result = [];

        foreach ($prices as $price) {
            $subCategoryPricesDto = $this->calculateSubCategoryPriceForPeriod($category, $price->period->id);

            $priceForOneLectureInRoubles = self::coinsToRoubles($price->price_for_one_lecture);
            $priceForOneLecturePromoInRoubles = self::coinsToRoubles($price->price_for_one_lecture_promo);
            $categoryPriceInRoubles = self::coinsToRoubles($subCategoryPricesDto->getUsualPrice());
            $categoryPromoPriceInRoubles = self::coinsToRoubles($subCategoryPricesDto->getPromoPrice());

            $result[] = [
                'title' => $price->period->title,
                'length' => $price->period->length,
                'price_for_one_lecture' => $priceForOneLectureInRoubles,
                'price_for_one_lecture_promo' => $priceForOneLecturePromoInRoubles,
                'price_for_category' => $categoryPriceInRoubles,
                'price_for_category_promo' => $categoryPromoPriceInRoubles,
            ];
        }

        return $result;
    }

//    public function calculateMainCategoryPriceForPeriod(
//        Category $mainCategory,
//        int      $periodId
//    ): CategoryPricesDto {
//        $price = 0;
//
//        foreach ($mainCategory->childrenCategories as $subCategory) {
//            $subCategoryPricesDto = $this->calculateSubCategoryPriceForPeriod($subCategory, $periodId);
//
//            $price += $subCategory->isPromo() ?
//                $subCategoryPricesDto->getPromoPrice() :
//                $subCategoryPricesDto->getPrice();
//        }
//
//        return new CategoryPricesDto(
//            $price,
//            $pricePromo
//        );
//    }

    public function calculateSubCategoryPriceForPeriod(
        Category $category,
        int      $periodId
    ): CategoryPricesDto {
        $price = 0;
        $pricePromo = 0;

        $lecturesCount = $category->lectures->count();

        if ($lecturesCount === 0) {
            return new CategoryPricesDto(
                $price,
                $pricePromo,
                $category
            );
        }

        foreach ($category->lectures as $lecture) {
            if ($lecture->isFree()) {
                return new CategoryPricesDto($price, $pricePromo, $category);
            }

            $lecturePrices = app(LectureService::class)
                ->calculateLecturePricesSubCategory($lecture);
            $lecturePromoPrices = app(LectureService::class)
                ->formPricesPromoLectureSubCategory($lecture);

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

        return new CategoryPricesDto($price, $pricePromo, $category);
    }

    public function calculateMainCategoryPriceForPeriod(
        Category $category,
        int      $periodId
    ): CategoryPricesDto {
        $price = 0;
        $pricePromo = 0;

        $lecturesCount = $category->childrenCategoriesLectures->count();

        if ($lecturesCount === 0) {
            return new CategoryPricesDto(
                $price,
                $pricePromo,
                $category
            );
        }

        foreach ($category->childrenCategoriesLectures as $lecture) {
            if ($lecture->isFree()) {
                return new CategoryPricesDto($price, $pricePromo, $category);
            }

            $lecturePrices = app(LectureService::class)
                ->formLecturePricesMainCategory($lecture);
            $lecturePromoPrices = app(LectureService::class)
                ->formPromoLecturePricesMainCategory($lecture);

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

        return new CategoryPricesDto($price, $pricePromo, $category);
    }

    private function calculatePriceForPeriod(?Category $category, int $periodId): CategoryPricesDto
    {
        if ($category->isSub()) {
            return $this->calculateSubCategoryPriceForPeriod($category, $periodId);
        }

        return $this->calculateMainCategoryPriceForPeriod($category, $periodId);
    }

    public function getCategoryPriceForPeriod(int $categoryId, int $periodId): int
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
        $categoryPricesDto = $this->calculatePriceForPeriod($category, $periodId);

        return $categoryPricesDto->getPrice();
    }
}
