<?php

namespace App\Services;

use App\Repositories\UserRepository;

class CategoryService
{
    public function __construct(
        private UserRepository $userRepository
    )
    {
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
            if ($subscription->isActual()) return true;
        }

        return false;
    }
}
