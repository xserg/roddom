<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Period;
use App\Repositories\CategoryRepository;
use App\Repositories\LectureRepository;
use App\Repositories\UserRepository;
use App\Traits\MoneyConversion;
use Illuminate\Database\Eloquent\Collection;

class LectureService
{
    use MoneyConversion;

    public function __construct(
        private LectureRepository  $lectureRepository,
        private UserRepository     $userRepository,
        private CategoryRepository $categoryRepository
    ) {
    }

    public function isLectureStrictPurchased(int $lectureId): bool
    {
        $lecturesSubscriptions = $this->userRepository
            ->lectureSubscriptions();

        if (
            is_null($lecturesSubscriptions)
            || $lecturesSubscriptions->isEmpty()
        ) {
            return false;
        }

        $lecturesSubscriptions = $lecturesSubscriptions
            ->where('subscriptionable_id', $lectureId);

        foreach ($lecturesSubscriptions as $subscription) {
            if ($subscription->isActual()) {
                return true;
            }
        }

        return false;
    }

    public function isLecturesCategoryPurchased(int $lectureId): bool
    {
        $lecture = $this->lectureRepository->getLectureById($lectureId);
        $lectureCategoryId = $lecture->category_id;

        $categoriesSubscriptions = $this->userRepository
            ->categorySubscriptions();

        if (
            is_null($categoriesSubscriptions)
            || $categoriesSubscriptions->isEmpty()
        ) {
            return false;
        }

        $categoriesSubscriptions = $categoriesSubscriptions
            ->where('subscriptionable_id', $lectureCategoryId);

        foreach ($categoriesSubscriptions as $subscription) {
            if ($subscription->isActual()) {
                return true;
            }
        }

        return false;
    }

    public function isLecturePromoPurchased(int $lectureId): bool
    {
        $lecture = $this->lectureRepository->getLectureById($lectureId);
        $promoSubscription = $this->userRepository->promoSubscriptions();

        if (
            is_null($promoSubscription)
            || $promoSubscription->isEmpty()
        ) {
            return false;
        }

        if ($lecture->isPromo()) {
            $stillActual = $promoSubscription->last()->isActual();

            return $stillActual;
        }

        return false;
    }

    public function formAllLecturePrices(): array
    {
        $prices = [];

        $mainCategories = Category::mainCategories()->with([
            'childrenCategories.categoryPrices.period',
            'childrenCategories.parentCategory',
            'childrenCategories.categoryPrices',
            'childrenCategories.lectures.category.categoryPrices',
            'childrenCategories.lectures.pricesInPromoPacks',
            'childrenCategories.lectures.pricesForLectures',
            'childrenCategories.lectures.pricesPeriodsInPromoPacks',
            'childrenCategories.lectures.paymentType',
            'childrenCategories.lectures.contentType',
        ])->get();

        foreach (Period::all() as $period) {
            $prices[] = [
                'period_id' => $period->id,
                'period_length' => $period->length,
                'price' => self::coinsToRoubles($this->calculateEverythingPriceByPeriod($mainCategories, $period->id))
            ];
        }

        return $prices;
    }

    public function calculateEverythingPriceByPeriod(Collection $mainCategories, int $periodId): int
    {
        $price = 0;

        foreach ($mainCategories as $category) {
            $price += $this->categoryRepository->calculateMainCategoryPriceForPeriod($category, $periodId);
        }

        return $price;
    }
}
