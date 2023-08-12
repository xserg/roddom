<?php

namespace App\Observers;

use App\Models\Category;
use App\Models\EverythingPack;
use App\Models\Lecture;
use App\Models\Promo;
use App\Models\Subscription;
use App\Repositories\CategoryRepository;

class SubscriptionObserver
{
    public function saving(Subscription $subscription): void
    {
        if ($subscription->subscriptionable_type === Lecture::class) {
            $entityTitle = 'Лекция: ' . Lecture::query()->find($subscription->subscriptionable_id)->title;
        } elseif ($subscription->subscriptionable_type === Category::class) {
            $entityTitle = 'Категория: ' . Category::query()->find($subscription->subscriptionable_id)->title;
        } elseif ($subscription->subscriptionable_type === Promo::class) {
            $entityTitle = 'Промопак лекций';
        } elseif ($subscription->subscriptionable_type === EverythingPack::class) {
            $entityTitle = 'Все лекции';
        } else {
            $entityTitle = 'Заголовок лекции не определён';
        }

        $subscription->entity_title = $entityTitle;
    }

    public function saved(Subscription $subscription): void
    {
        $type = $subscription->subscriptionable_type;
        $id = $subscription->subscriptionable_id;

        if ($subscription->isDirty(['subscriptionable_type', 'subscriptionable_id']))
            if ($type === Lecture::class) {
                $subscription->lectures()->sync([$id]);
            } elseif ($type === Category::class) {
                $categoryLectures = app(CategoryRepository::class)->getAllLecturesByCategory($id);
                $subscription->lectures()->sync($categoryLectures);
            } elseif ($type === Promo::class) {
                $promoLectures = Lecture::promo()->get('id');
                $subscription->lectures()->sync($promoLectures);
            } elseif ($type === EverythingPack::class) {
                $lectures = Lecture::all('id');
                $subscription->lectures()->sync($lectures);
            }
    }
}
