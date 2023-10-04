<?php

namespace App\Dto;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class EverythingPackPurchaseDto
{
    /**
     * @param bool $isPromo
     * @param int|null $lecturesBoughtCount
     * @param int|null $initialPrice
     * @param int|null $initialPromoPrice
     * @param int|null $usualPriceToPay
     * @param int|null $promoPriceToPay
     * @param bool $is_discounted
     * @param float|null $intersectPercent какой процент составляет пересечение
     *                            лекций из активной подписки и тех, которые собираемся купить
     * @param int|null $intersectCount
     * @param int|null $discountOn
     * @param int|null $discountOnPromo
     * @param array|Collection|EloquentCollection $excluded
     */
    public function __construct(
        private bool                                $isPromo,
        private ?int                                $lecturesBoughtCount = null,
        private ?int                                $initialPrice = null,
        private ?int                                $initialPromoPrice = null,
        private ?int                                $usualPriceToPay = null,
        private ?int                                $promoPriceToPay = null,
        private bool                                $is_discounted = false,
        private ?float                              $intersectPercent = null,
        private ?int                                $intersectCount = null,
        private ?int                                $discountOn = null,
        private ?int                                $discountOnPromo = null,
        private array|Collection|EloquentCollection $excluded = []
    ) {
    }

    public function getInitialUsualPrice(): ?int
    {
        return $this->initialPrice;
    }

    public function getInitialPromoPrice(): ?int
    {
        return $this->initialPromoPrice;
    }

    public function getInitialPrice(): ?int
    {
        return $this->isPromo ?
            $this->getInitialPromoPrice() :
            $this->getInitialUsualPrice();
    }

    public function getIntersectPercent(): ?float
    {
        return round($this->intersectPercent, 2);
    }

    public function getDiscountOn(): ?int
    {
        return $this->discountOn;
    }

    public function getDiscountOnPromo(): ?int
    {
        return $this->discountOnPromo;
    }

    public function isDiscounted(): bool
    {
        return $this->is_discounted;
    }

    public function getIntersectCount(): ?int
    {
        return $this->intersectCount;
    }

    public function getExcluded(): array|Collection|EloquentCollection
    {
        return $this->excluded;
    }

    public function getPriceToPay(): ?int
    {
        return $this->isPromo ?
            $this->getPromoPriceToPay() :
            $this->getUsualPriceToPay();
    }

    public function getUsualPriceToPay(): ?int
    {
        return $this->usualPriceToPay;
    }

    public function getPromoPriceToPay(): ?int
    {
        return $this->promoPriceToPay;
    }

    public function getLecturesBoughtCount(): ?int
    {
        return $this->lecturesBoughtCount;
    }

    public function isPromo(): bool
    {
        return $this->isPromo;
    }
}
