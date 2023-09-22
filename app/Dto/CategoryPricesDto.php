<?php

namespace App\Dto;

use App\Models\Category;

class CategoryPricesDto
{
    public function __construct(
        private int      $price,
        private int      $promoPrice,
        private Category $category
    ) {
    }

    public function getUsualPrice(): int
    {
        return $this->price;
    }

    public function getPromoPrice(): int
    {
        return $this->promoPrice;
    }

    public function getPrice(): int
    {
        return $this->category->isPromo() ?
            $this->getPromoPrice() :
            $this->getUsualPrice();
    }
}
