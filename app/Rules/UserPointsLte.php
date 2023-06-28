<?php

namespace App\Rules;

use App\Traits\MoneyConversion;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UserPointsLte implements ValidationRule
{
    use MoneyConversion;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $userPoints = self::coinsToRoubles(auth()->user()->refPoints?->points) ?? 0;

        if ((float) $value > $userPoints) {
            $fail("Значение {$attribute} аттрибута может быть равно или меньше, чем количество реф поинтов у юзера: {$userPoints}.");
        }
    }
}
