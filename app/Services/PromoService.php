<?php

namespace App\Services;

class PromoService
{
    public function isPromoPurchased()
    {
        $purchasedCategoriesIds = auth()->user()
            ->promoSubscriptions()
            ->get()
            ->pluck('subscriptionable_id');

        return $purchasedCategoriesIds->contains(1);
    }
}
