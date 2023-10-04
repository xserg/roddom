<?php

namespace App\Dto;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class DiscountsDto
{
    public function __construct(
        private bool                                $status = false,
        private null|int|float                      $excludedPercent = null,
        private ?int                                $excludedCount = null,
        private ?int                                $discountedCurrency = null,
        private ?int                                $discountedCurrencyPromo = null,
        private array|Collection|EloquentCollection $excluded = []
    ) {
    }

    public function getExcludedPercent(): float|int|null
    {
        return $this->excludedPercent;
    }

    public function getDiscountedCurrency(): ?int
    {
        return $this->discountedCurrency;
    }

    public function getDiscountedCurrencyPromo(): ?int
    {
        return $this->discountedCurrencyPromo;
    }

    public function getStatus(): bool
    {
        return $this->status;
    }

    public function getExcludedCount(): ?int
    {
        return $this->excludedCount;
    }

    public function getExcluded(): array|Collection|EloquentCollection
    {
        return $this->excluded;
    }

}
