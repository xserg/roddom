<?php

namespace App\Repositories;

use App\Models\Period;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class PeriodRepository
{
    private Collection $periods;

    public function __construct()
    {
        $this->periods = $this->getAllCached();
    }

    public function getPeriodByLength(int $length): ?Period
    {
        return Period::query()->firstWhere('length', $length);
    }

    public function getPeriodById(int $id): ?Period
    {
        return Period::query()->firstWhere('id', $id);
    }

    public function getAllCached()
    {
        return Cache::rememberForever('periods', fn () => Period::all());
    }

    public function getAll()
    {
        return $this->periods;
    }
}
