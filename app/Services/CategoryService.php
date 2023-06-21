<?php

namespace App\Services;

use App\Dto\CategoryPricesDto;
use App\Models\Category;
use App\Models\Period;
use App\Repositories\UserRepository;
use App\Traits\MoneyConversion;
use Illuminate\Support\Arr;

class CategoryService
{
    use MoneyConversion;

    public function __construct(
        private UserRepository $userRepository,
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

            $categoryPriceInRoubles = self::coinsToRoubles($categoryPriceDto->getPrice());
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
            $categoryPriceInRoubles = self::coinsToRoubles($subCategoryPricesDto->getPrice());
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
                $pricePromo
            );
        }

        foreach ($category->lectures as $lecture) {
            if($lecture->isFree()){
                return new CategoryPricesDto($price, $pricePromo);
            }

            $lecturePrices = app(LectureService::class)
                ->formLecturePricesSubCategory($lecture);
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

        return new CategoryPricesDto($price, $pricePromo);
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
                $pricePromo
            );
        }

        foreach ($category->childrenCategoriesLectures as $lecture) {
            if($lecture->isFree()){
                return new CategoryPricesDto($price, $pricePromo);
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

        return new CategoryPricesDto($price, $pricePromo);
    }


    /**
     * Считать цену учитывая только общую цену на лекции и количество лекций в категории
     */
    public function getCategoryPrice(int $categoryId, int $periodId): int|string
    {
        $category = $this->getCategoryById($categoryId);

        if (! is_null($category)) {
            return 0;
        }

        $prices = $category->categoryPrices;
        $lecturesCount = $category->lectures->count();

        foreach ($prices as $price) {
            $priceForPackInRoubles =
                number_format(($price->price_for_one_lecture * $lecturesCount) / 100, 2, thousands_separator: '');

            if ($price->period->id == $periodId) {
                return $priceForPackInRoubles;
            }
        }

        return 0;
    }
}
