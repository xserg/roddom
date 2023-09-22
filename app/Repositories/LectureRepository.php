<?php

namespace App\Repositories;

use App\Models\Lecture;
use App\QueryBuilders\LectureCategoryFilter;
use App\Traits\MoneyConversion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LectureRepository
{
    use MoneyConversion;

    public function getLectureById($id): ?Lecture
    {
        $lecture = Lecture::query()
            ->with('lector', 'lector.diplomas')
            ->find($id);

        if (! $lecture) {
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

    public function getAllQueryWith($relations = []): Builder
    {
        $builder = Lecture::query()->with($relations);

        return $builder;
    }

    public function getAllPromoQueryWith($relations = []): Builder
    {
        $builder = Lecture::query()->promo()->with($relations);

        return $builder;
    }

    public function addFiltersToQuery(
        Builder|QueryBuilder $builder,
    ): Builder|QueryBuilder {
        $builder = QueryBuilder::for($builder)
            ->allowedSorts(['created_at'])
            ->allowedIncludes(['category', 'lector', 'lector.diplomas'])
            ->allowedFilters([
                AllowedFilter::scope('watched'),
                AllowedFilter::scope('list-watched'),
                AllowedFilter::scope('saved'),
                AllowedFilter::scope('purchased'),
                AllowedFilter::scope('recommended'),
                AllowedFilter::scope('not_watched'),
                AllowedFilter::exact('lector_id'),
                AllowedFilter::custom('category_id', new LectureCategoryFilter()),
            ]);

        return $builder;
    }

    public function paginate(
        QueryBuilder|Builder|Collection $builder,
        ?int                            $perPage,
        ?int                            $page,
    ): LengthAwarePaginator {
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

    /**
     * Возвращает массив типа [0 => 320, 1 => 252, 2 => 138],
     * где values - lecture ids
     */
    public function getAllPurchasedLectureIdsForCurrentUser(): array
    {
        $subscriptions = auth()->user()->actualSubscriptions()->with('lectures')->get();

        return $subscriptions
            ->map(fn ($subscription) => $subscription->lectures?->modelKeys())
            ->flatten()
            ->unique()
            ->toArray();
    }
}
