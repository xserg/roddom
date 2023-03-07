<?php

namespace App\Http\Resources;

use App\Repositories\LectureRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class LectureCollection extends ResourceCollection
{
    private $lectureRepository;

    public function __construct($resource)
    {
        parent::__construct($resource);
        $this->lectureRepository = app(LectureRepository::class);
    }

    public function toArray(Request $request): array
    {
        $purchasedLectureIds = $this
            ->lectureRepository
            ->getAllPurchasedLectureIdsByUser(
                auth()->user()
            );

        $collection = $this->collection->map(function ($lecture) use ($purchasedLectureIds) {
            $isPurchased = in_array($lecture->id, $purchasedLectureIds);
            $isWatched = auth()->user()->watchedLectures->contains($lecture->id);
            $isPromo = $lecture->promoPacks->isNotEmpty();

            $lecture->is_watched = $isWatched;
            $lecture->is_promo = $isPromo;
            $lecture->is_purchased = $isPurchased;

            return $lecture;
        });

        return [
            'data' => $collection
        ];
    }
}
