<?php

namespace App\Repositories;

use App\Models\Lecture;
use App\Models\LecturePaymentType;
use App\Models\Period;
use App\Models\Promo;
use App\Models\User;
use App\Traits\MoneyConversion;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
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
            ->where(['id' => $id])
            ->first();

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
                AllowedFilter::exact('category_id'),
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
}
