<?php

namespace App\Services;

use App\Repositories\LectureRepository;
use App\Repositories\UserRepository;

class LectureService
{
    public function __construct(
        private LectureRepository $lectureRepository,
        private UserRepository $userRepository
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
}
