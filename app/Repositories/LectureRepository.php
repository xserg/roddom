<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\Lecture;
use App\Models\Promo;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LectureRepository
{
    public function __construct(
        private CategoryRepository $categoryRepository,
        private PromoRepository    $promoRepository
    )
    {
    }

    /**
     * @param $id
     * @return Lecture|null
     * @throws NotFoundHttpException
     */
    public function getLectureById($id): ?Lecture
    {
        $lecture = Lecture::query()
            ->with('lector', 'lector.diplomas')
            ->where(['id' => $id])
            ->first();

        if (!$lecture) {
            throw new NotFoundHttpException('Лекция c id ' . $id . ' не найдена');
        }

        return $lecture;
    }

    public function getLectureByIdQuery($id, $relations = []): Builder
    {
        $query = Lecture::query()
            ->with($relations)
            ->where(['id' => $id]);

        return $query;
    }

    public function getAllQuery($relations = []): Builder
    {
        $builder = Lecture::query()->with($relations);
        return $builder;
    }

    public function getAllPromoQuery($relations = []): Builder
    {
        $builder = Lecture::query()->promo()->with($relations);
        return $builder;
    }

    public function addFiltersToQuery(
        Builder|QueryBuilder $builder,
    ): Builder|QueryBuilder
    {
        $builder = QueryBuilder::for($builder)
//            ->defaultSort('-created_at')
            ->allowedSorts(['created_at'])
            ->allowedIncludes(['category', 'lector', 'lector.diplomas'])
            ->allowedFilters([
                AllowedFilter::scope('watched'),
                AllowedFilter::scope('saved'),
                AllowedFilter::scope('purchased'),
                AllowedFilter::scope('recommended'),
                AllowedFilter::scope('not_watched'),
                AllowedFilter::exact('lector_id'),
                AllowedFilter::exact('category_id'),
            ]);

        return $builder;
    }

    public function getAllWithFlags(Builder|QueryBuilder $builder): Collection
    {
        $lectures = $builder->get();
        $purchasedLectureIds = $this
            ->getAllPurchasedLecturesIdsAndTheirDatesByUser(
                auth()->user()
            );
        $watchedLectures = auth()->user()->watchedLectures;

        $lectures = $lectures->map(function ($lecture) use ($purchasedLectureIds, $watchedLectures) {
            $isPurchased = in_array($lecture->id, $purchasedLectureIds);
            $isWatched = $watchedLectures->contains($lecture->id);
            $isPromo = $lecture->promoPacks->isNotEmpty();

            $lecture->is_watched = (int)$isWatched;
            $lecture->is_promo = (int)$isPromo;
            $lecture->is_purchased = (int)$isPurchased;

            return $lecture;
        });

        return $lectures;
    }

    public function paginate(
        QueryBuilder|Builder|Collection $builder,
        ?int                            $perPage,
        ?int                            $page,
    ): LengthAwarePaginator
    {
        $lectures = $builder
            ->paginate(
                perPage: $perPage,
                page: $page
            )->withQueryString();

        if ($lectures->isEmpty()) {
            throw new NotFoundHttpException(
                'Not found any lecture with such parameters'
            );
        }

        return $lectures;
    }

//    private function withCategories($builder, $categoryId)
//    {
//        if ($categoryId) {
//            $category = LectureCategory::query()
//                ->where('id', '=', $categoryId)
//                ->first();
//
//            if (!$category) {
//                throw new NotFoundHttpException('Not found any lecture with such category id: ' . $categoryId);
//            }
//
//            $isSub = $this->categoryService->isCategorySub($category);
//
//            if ($isSub) {
//                $builder = $builder
//                    ->where('category_id', '=', $category->id);
//            } else {
//                $categoryIds = LectureCategory::query()
//                    ->select('id')
//                    ->where('parent_id', '=', $category->id)
//                    ->get()
//                    ->pluck('id')
//                    ->toArray();
//
//                $builder = $builder
//                    ->whereIn('category_id', $categoryIds);
//            }
//
//        }
//        return $builder;
//    }

    public function getAllPurchasedLecturesIdsAndTheirDatesByUser(
        Authenticatable|User $user
    ): array
    {
        $lectures = [];

        $lecturesSubscriptions = $user
            ->subscriptions
            ->where('subscriptionable_type', Lecture::class);

        $categorySubscriptions = $user
            ->subscriptions
            ->where('subscriptionable_type', Category::class);

        $promoSubscriptions = $user
            ->subscriptions
            ->where('subscriptionable_type', Promo::class);

        foreach ($lecturesSubscriptions as $lecturesSubscription) {
            if ($lecturesSubscription['end_date'] < now()) continue;

            $lectures[$lecturesSubscription['subscriptionable_id']] = [
                'start_date' => $lecturesSubscription['start_date'],
                'end_date' => $lecturesSubscription['end_date'],
            ];
        }

        foreach ($categorySubscriptions as $categorySubscription) {
            if ($categorySubscription['end_date'] < now()) continue;

            $category = $this->categoryRepository->getCategoryById($categorySubscription['subscriptionable_id']);
            $categoryLectures = $category->lectures;
            foreach ($categoryLectures as $lecture) {
                $lectures[$lecture->id] = [
                    'start_date' => $categorySubscription['start_date'],
                    'end_date' => $categorySubscription['end_date'],
                ];
            }
        }

        foreach ($promoSubscriptions as $promoSubscription) {
            if ($promoSubscription['end_date'] < now()) continue;

            $promo = $this->promoRepository->getById($promoSubscription['subscriptionable_id']);
            $promoLectures = $promo->promoLectures;
            foreach ($promoLectures as $lecture) {
                $lectures[$lecture->id] = [
                    'start_date' => $promoSubscription['start_date'],
                    'end_date' => $promoSubscription['end_date'],
                ];
            }
        }

        return $lectures;
    }

    public function setFlagsToLectures(
        Collection $lectures
    ): Collection
    {
        $watchedLectures = auth()->user()->watchedLectures;
        $purchasedLectures = $this->getAllPurchasedLecturesIdsAndTheirDatesByUser(auth()->user());
        $promoLecturesIds = $this->getAllPromoQuery()->get();

        $lectures = $lectures->map(function ($lecture) use ($watchedLectures, $purchasedLectures, $promoLecturesIds) {
            /**
             * @var $lecture Lecture
             */
            $lecture->setAppends([]);

            $isWatched = $watchedLectures->contains($lecture->id);
            $isPromo = $promoLecturesIds->contains($lecture->id);
            $isPurchased = (int)array_key_exists($lecture->id, $purchasedLectures);

            $purchaseInfo = [
                'is_purchased' => (int)array_key_exists($lecture->id, $purchasedLectures),
                'end_date' => $isPurchased == 1 ? $purchasedLectures[$lecture->id]['end_date'] : null
            ];

            $categoryPrices = $lecture->category->categoryPrices;
            $prices = [];

            foreach ($categoryPrices as $price) {
                $priceForLecture = number_format($price->price_for_one_lecture / 100, 2);
                $prices['price_by_category'][] = [
                    'title' => $price->period->title,
                    'length' => $price->period->length,
                    'price_for_lecture' => $priceForLecture
                ];
            }

            $periods = $lecture->pricesPeriodsInPromoPacks;
            if ($periods->isNotEmpty()) {
                foreach ($periods as $period) {
                    $priceForLecture = number_format($period->pivot->price / 100, 2);
                    $prices['price_by_promo'][$period->length] = [
                        'title' => $period->title,
                        'length' => $period->length,
                        'price_for_promo_lecture' => $priceForLecture,
                    ];
                }
            }

            $lecture->is_watched = (int)$isWatched;
            $lecture->is_promo = (int)$isPromo;
            $lecture->purchase_info = $purchaseInfo;
            $lecture->prices = $prices;

            return $lecture;
        });

        return $lectures;
    }

    public function getPurchasedLecturesByUser(Authenticatable|User $user): Collection
    {
        $purchasedLectureIds = $this->getAllPurchasedLecturesIdsAndTheirDatesByUser($user);
        return Lecture::whereIn('id', array_keys($purchasedLectureIds))->get();
    }

    public function getPurchasedInfoByUser(Authenticatable|User $user): Collection
    {

    }

    public function getLecturePrice(Lecture $lecture, int $period): int|float
    {
        $prices = $lecture->prices;
        if ($lecture->is_promo == 1) {
            $priceArr = Arr::where(
                $prices['price_by_promo'],
                fn($value) => $value['length'] == $period
            );
            $price = Arr::first($priceArr)['price_for_promo_lecture'];
        } else {
            $priceArr = Arr::where(
                $prices['price_by_category'],
                fn($value) => $value['length'] == $period
            );
            $price = Arr::first($priceArr)['price_for_lecture'];
        }

        return $price;
    }
}
