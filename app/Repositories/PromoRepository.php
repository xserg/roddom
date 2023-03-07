<?php

namespace App\Repositories;

use App\Models\Lecture;
use App\Models\Promo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PromoRepository
{
    public function getById(int $id): Promo
    {
        return Promo::query()
            ->where('id', '=', $id)
            ->firstOrFail();
    }

    public function getAllWithPaginator(
        ?int $perPage,
        ?int $page,
    ): LengthAwarePaginator
    {
        $lectures = Lecture
            ::promo()
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
