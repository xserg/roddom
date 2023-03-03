<?php

namespace App\Http\Resources;

use App\Models\Category;
use App\Models\Lecture;
use App\Models\Period;
use App\Models\Promo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $categorySubscription = $this->subscriptionable_type === Category::class;
        $lectureSubscription = $this->subscriptionable_type === Lecture::class;
        $promoSubscription = $this->subscriptionable_type === Promo::class;

        return [
            'category_id' => $this->when(
                $categorySubscription,
                $this->subscriptionable_id
            ),
            'category_slug' => $this->when(
                $categorySubscription,
                Category::find($this->subscriptionable_id)->slug
            ),
            'lecture_id' => $this->when(
                $lectureSubscription,
                $this->subscriptionable_id
            ),
            'lecture_title' => $this->when(
                $lectureSubscription,
                Lecture::find($this->subscriptionable_id)->title
            ),
            'promo' => $this->when(
                $promoSubscription,
                'Акционный пакет лекций'
            ),
            'period' => Period::find($this->period_id)->length . ' day/days',
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
        ];
    }
}
