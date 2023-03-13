<?php

namespace App\Repositories;

use App\Models\Period;

class PeriodRepository
{
    public function getPeriodByLength(int $length): Period
    {
        return Period::query()->firstWhere('length', $length);
    }
}
