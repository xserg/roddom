<?php

namespace App\Repositories;

use App\Models\Lector;
use App\Models\Lecture;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class LectureRepository
{
    public function getLectureById($id)
    {
        $lecture = Lecture::query()
            ->with('lector')
            ->where(['id' => $id])
            ->first();

        return $lecture;
    }

    public function getAllWithPaginator(
        ?int $perPage,
        ?int $page
    ): LengthAwarePaginator
    {
        $lectures = Lecture::query()
            ->with('category')
            ->paginate(perPage: $perPage, page: $page);
        return $lectures;
    }
}
