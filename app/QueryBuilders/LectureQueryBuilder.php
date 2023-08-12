<?php

namespace App\QueryBuilders;

use App\Models\LecturePaymentType;
use App\Repositories\LectureRepository;
use Illuminate\Database\Eloquent\Builder;

class LectureQueryBuilder extends Builder
{
    public function watched(): Builder
    {
        if (! $user = auth()->user()) {
            return $this;
        }

        $watchedIds = $user->watchedLectures()->pluck('lectures.id');
        $this->whereIn('id', $watchedIds);

        if ($watchedIds->isNotEmpty()) {
            $ids = $watchedIds->implode(',');
            $this->orderByRaw("FIELD(id, $ids)");
        }

        return $this;
    }

    public function listWatched(): Builder
    {
        if (! $user = auth()->user()) {
            return $this;
        }

        $listWatchedIds = $user->listWatchedLectures()->pluck('lectures.id');

        $this->whereIn('id', $listWatchedIds);

        if ($listWatchedIds->isNotEmpty()) {
            $ids = $listWatchedIds->implode(',');
            $this->orderByRaw("FIELD(id, $ids)");
        }

        return $this;
    }

    public function saved(): Builder
    {
        if (! $user = auth()->user()) {
            return $this;
        }

        $savedIds = $user->savedLectures()->pluck('id');

        $this->whereIn('id', $savedIds);

        if ($savedIds->isNotEmpty()) {
            $ids = $savedIds->implode(',');
            $this->orderByRaw("FIELD(id, $ids)");
        }

        return $this;
    }

    public function promo(): Builder
    {
        return $this->where('payment_type_id', LecturePaymentType::PROMO);
    }

    public function notPromo(): Builder
    {
        return $this->where('payment_type_id', '!=', LecturePaymentType::PROMO);
    }

    public function purchased(): Builder
    {
        if (! auth()->user()) {
            return $this;
        }

        $purchasedLecturesIds = app(LectureRepository::class)->getAllPurchasedLectureIdsForCurrentUser();

        return $this->whereIn('id', $purchasedLecturesIds);
        //            ->orderByRaw("FIELD(id, $ids)");
    }

    public function free(): Builder
    {
        return $this->where('payment_type_id', LecturePaymentType::FREE);
    }

    public function payed(): Builder
    {
        return $this->where('payment_type_id', '!=', LecturePaymentType::FREE);
    }

    public function notWatched(): Builder
    {
        if (! auth()->user()) {
            return $this;
        }

        return $this->whereDoesntHave('watchedUsers', function ($query) {
            $query->where('user_id', auth()->id());
        });
    }

    public function recommended(): Builder
    {
        return $this->where('is_recommended', true);
    }
}
