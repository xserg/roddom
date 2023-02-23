<?php

namespace App\Repositories;

use App\Models\Lecture;
use App\Models\LectureCategory;
use App\Services\CategoryService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LectureRepository
{
    public function __construct(
        private CategoryService $categoryService
    )
    {
    }

    public function getLectureById($id)
    {
        $lecture = Lecture::query()
            ->with('lector')
            ->where(['id' => $id])
            ->first();

        return $lecture;
    }

    public function getAllWithPaginator(
        ?int    $perPage,
        ?int    $page,
        ?string $categorySlug,
        ?int    $lectorId,
    ): LengthAwarePaginator
    {
        $builder = Lecture::query()->with('category');

        if ($categorySlug) {
            $category = LectureCategory::query()
                ->where('slug', '=', $categorySlug)
                ->first();

            if (! $category) {
                throw new NotFoundHttpException('Not found any lecture with such parameters: ' . $categorySlug);
            }

            $isSub = $this->categoryService->isCategorySub($category);

            if ($isSub) {
                $builder = $builder
                    ->where('category_id', '=', $category->id);
            } else {
                $categoryIds = LectureCategory::query()
                    ->select('id')
                    ->where('parent_id', '=', $category->id)
                    ->get()
                    ->pluck('id')
                    ->toArray();

                $builder = $builder
                    ->whereIn('category_id', $categoryIds);
            }
        }

        if($lectorId){
            $builder = $builder->where('lector_id', '=', $lectorId);
        }

        $lectures = $builder
            ->orderBy('id', 'DESC')
            ->paginate(
                perPage: $perPage,
                page: $page
            )->withQueryString();

        if ($lectures->isEmpty()) {
            throw new NotFoundHttpException('Not found any lecture with such parameters');
        }

        return $lectures;
    }
}
