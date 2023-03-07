<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\Lecture;
use App\Models\Promo;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LectureRepository
{
    public function __construct(
        private CategoryRepository $categoryRepository,
        private PromoRepository $promoRepository
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

    public function getAllWithPaginator(
        ?int $perPage,
        ?int $page,
    ): LengthAwarePaginator
    {
        $builder = Lecture::query();

        $builder = QueryBuilder::for($builder)
            ->defaultSort('-created_at')
            ->allowedSorts(['created_at'])
            ->allowedIncludes(['category', 'lector', 'lector.diplomas'])
            ->allowedFilters([
                AllowedFilter::scope('watched'),
                AllowedFilter::scope('saved'),
                AllowedFilter::scope('purchased'),
                AllowedFilter::exact('lector_id'),
                AllowedFilter::exact('category_id'),
            ]);

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

    public function getAllPurchasedLectureIdsByUser(
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

        foreach ($lecturesSubscriptions as $subscription) {
            $lectures[] = $subscription['subscriptionable_id'];
        }

        foreach ($categorySubscriptions as $categorySubscription) {
            $category = $this->categoryRepository->getCategoryById($categorySubscription['subscriptionable_id']);
            $categoryLectures = $category->lectures;
            foreach ($categoryLectures as $lecture) {
                $lectures[] = $lecture->id;
            }
        }

        foreach ($promoSubscriptions as $promoSubscription){
            $promo = $this->promoRepository->getById($promoSubscription['subscriptionable_id']);
            $promoLectures = $promo->promoLectures;
            foreach ($promoLectures as $lecture){
                $lectures[] = $lecture->id;
            }
        }

        return $lectures;
    }
}
