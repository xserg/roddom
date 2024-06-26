<?php

namespace App\Observers;

use App\Models\Category;
use App\Models\EverythingPack;
use App\Models\Lecture;
use App\Models\Promo;
use App\Models\Subscription;
use App\Repositories\CategoryRepository;
use App\Services\PurchaseService;
use Illuminate\Database\Eloquent\Collection;

class SubscriptionObserver
{
    public function __construct(
        private readonly PurchaseService $purchaseService
    ) {
    }

    public function saving(Subscription $subscription): void
    {
        $entityTitle = $this->purchaseService->resolveEntityTitle(
            $subscription->subscriptionable_type,
            $subscription->subscriptionable_id
        );
        $subscription->entity_title = $entityTitle;
    }

    public function updated(Subscription $subscription): void
    {
        $this->syncPurchasedLectures($subscription);
    }

    public function created(Subscription $subscription): void
    {
        $count = $this->syncPurchasedLectures($subscription, true);

        if ($subscription->lectures_count == 0) {
            $subscription->lectures_count = $count;
            $subscription->save();
        }
    }

    private function syncPurchasedLectures(Subscription $subscription, bool $isCreated = false): int
    {
        $type = $subscription->subscriptionable_type;
        $id = $subscription->subscriptionable_id;

        if (! $subscription->isDirty(['subscriptionable_type', 'subscriptionable_id'])) {
            return 0;
        }

        if ($type === Lecture::class) {
            $subscription->lectures()->sync([$id]);
            return 1;
        } elseif ($type === Category::class) {

            $categoryLectures = app(CategoryRepository::class)->getAllLecturesByCategory($id);
            $purchasedLectures = $categoryLectures
                ->when($isCreated, fn (Collection $collection) => $collection->except($subscription->exclude));

            $subscription->lectures()->sync($purchasedLectures);
            return $purchasedLectures->count();
        } elseif ($type === Promo::class) {

            $promoLectures = Lecture::promo()->get('id');
            $subscription->lectures()->sync($promoLectures);
            return $promoLectures->count();
        } elseif ($type === EverythingPack::class) {

            $lectures = Lecture::all('id');
            $purchasedLectures = $lectures
                ->when($isCreated, fn (Collection $collection) => $collection->except($subscription->exclude));

            $subscription->lectures()->sync($purchasedLectures);
            return $purchasedLectures->count();
        }

        return 0;
    }
}
