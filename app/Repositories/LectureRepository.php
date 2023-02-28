<?php

namespace App\Repositories;

use App\Models\Lecture;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LectureRepository
{
    public function __construct(
    )
    {
    }

    public function getLectureById($id)
    {
        $lecture = Lecture::query()
            ->with('lector', 'lector.diplomas')
            ->where(['id' => $id])
            ->first();

        return $lecture;
    }

    public function getAllWithPaginator(
        ?int $perPage,
        ?int $page,
    ): LengthAwarePaginator
    {
        $builder = Lecture::query()
            ->with('lector.diplomas');

        $builder = QueryBuilder::for($builder)
            ->defaultSort('-created_at')
            ->allowedSorts(['created_at'])
            ->allowedIncludes(['category', 'lector'])
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
                'Not found any lecture with such parameters:per_page=' . $perPage . ', page=' . $page . '.'
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

}
