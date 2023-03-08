<?php

namespace App\Repositories;

use App\Models\Promo;

class PromoRepository
{
    public function getById(int $id): Promo
    {
        return Promo::query()
            ->where('id', '=', $id)
            ->firstOrFail();
    }
}
