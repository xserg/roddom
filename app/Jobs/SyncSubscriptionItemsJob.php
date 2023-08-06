<?php

namespace App\Jobs;

use App\Models\Category;
use App\Models\EverythingPack;
use App\Models\Lecture;
use App\Models\Promo;
use App\Models\Subscription;
use App\Repositories\CategoryRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncSubscriptionItemsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly Subscription $subscription,
    ) {
    }

    public function handle(CategoryRepository $categoryRepository): void
    {
        $type = $this->subscription->subscriptionable_type;
        $id = $this->subscription->subscriptionable_id;

        if ($type === Lecture::class) {
            $this->subscription->lectures()->sync([$id]);
        } elseif ($type === Category::class) {
            $categoryLectures = $categoryRepository->getAllLecturesByCategory($id);
            $this->subscription->lectures()->sync($categoryLectures);
        } elseif ($type === Promo::class) {
            $promoLectures = Lecture::promo()->get('id');
            $this->subscription->lectures()->sync($promoLectures);
        } elseif ($type === EverythingPack::class) {
            $lectures = Lecture::all('id');
            $this->subscription->lectures()->sync($lectures);
        }
    }
}
