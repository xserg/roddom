<?php

namespace App\Services;

use App\Models\Lecture;
use App\Repositories\LectureRepository;

class LectureService
{
    public function __construct(
//        private LectureRepository $lectureRepository
    )
    {
    }

    public function isLecturePurchased($id)
    {
        $purchasedLecturesIds = auth()->user()
            ->lectureSubscriptions()
            ->get()
            ->pluck('subscriptionable_id');

        return $purchasedLecturesIds->contains($id);
    }

    public function isFree($lecture)
    {
        return $lecture->is_free == 1;
    }
}
