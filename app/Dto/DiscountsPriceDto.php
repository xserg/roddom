<?php

namespace App\Dto;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class DiscountsPriceDto
{
    public function __construct(
        private bool                                $status = false,
        private null|int|float                      $decreasedPercent = null,
        private ?int                                $decreasedCount = null,
        private null|int|float                      $decreasedCurrency = null,
        private null|int|float                      $decreasedCurrencyPromo = null,
        private array|Collection|EloquentCollection $excluded = []
    ) {
    }

    public function getDecreasedPercent(): float|int|null
    {
        return $this->decreasedPercent;
    }

    public function getDecreasedCurrency(): float|int|null
    {
        return $this->decreasedCurrency;
    }

    public function getDecreasedCurrencyPromo(): float|int|null
    {
        return $this->decreasedCurrencyPromo;
    }

    public function getStatus(): bool
    {
        return $this->status;
    }

    public function getDecreasedCount(): ?int
    {
        return $this->decreasedCount;
    }

    public function getExcluded(): array|Collection|EloquentCollection
    {
        return $this->excluded;
    }

}
