<?php

namespace App\Traits;

trait MoneyConversion
{
    public static function roublesToCoins(string|float $value): string
    {
        return $value * 100;
    }

    public static function coinsToRoubles(null|string|int $value): ?string
    {
        if (is_null($value)) {
            return null;
        }
        return number_format($value / 100, 2, thousands_separator: '');
    }
}
