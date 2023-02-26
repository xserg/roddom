<?php

namespace App\Repositories;

use App\Models\Lector;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LectorRepository
{
    public function getAllWithPaginator(
        ?int $perPage,
        ?int $page
    ): LengthAwarePaginator
    {
        $lectors = Lector::query()
            ->with('diplomas')
            ->paginate(
                perPage: $perPage,
                page: $page
            );

        return $lectors;
    }
}
