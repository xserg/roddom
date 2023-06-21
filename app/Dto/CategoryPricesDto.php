<?php

namespace App\Dto;

class CategoryPricesDto
{
    public function __construct(
        private int $price,
        private int $promoPrice,
    ) {
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function getPromoPrice(): int
    {
        return $this->promoPrice;
    }
}
