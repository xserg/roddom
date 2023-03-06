<?php

namespace App\Services;

use App\Models\Promo;
use App\Models\User;
use App\Repositories\LectureRepository;
use Illuminate\Contracts\Auth\Authenticatable;

class LectureService
{
    public function __construct(
        private LectureRepository $lectureRepository
    )
    {
    }

    public function isLectureStrictPurchased($id, User|Authenticatable $user): bool
    {
        $purchasedLecturesIds = $user
            ->lectureSubscriptions()
            ->get()
            ->pluck('subscriptionable_id');

        return $purchasedLecturesIds->contains($id);
    }

    public function isLecturesCategoryPurchased($lectureId, User|Authenticatable $user): bool
    {
        $lecture = $this->lectureRepository->getLectureById($lectureId);
        $lectureCategoryId = $lecture->category_id;

        $purchasedCategoriesIds = $user
            ->categorySubscriptions()
            ->get()
            ->pluck('subscriptionable_id');

        return $purchasedCategoriesIds->contains($lectureCategoryId);
    }

    public function isLecturesPromoPurchased($lectureId, User|Authenticatable $user): bool
    {
        $promoPackIds = $user
            ->promoSubscriptions()
            ->get()
            ->pluck('subscriptionable_id');

        foreach ($promoPackIds as $promoPackId) {
            $promoPack = Promo::query()->where('id', $promoPackId)->first();

            if ($promoPack->promoLectures()->contains($lectureId)) {
                return true;
            }
        }

        return false;
    }

    public function isFree(int $id): bool
    {
        return $this->lectureRepository->getLectureById($id)->is_free == 1;
    }
}
