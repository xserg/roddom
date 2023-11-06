<?php

namespace Tests\Unit\Traits;

use App\Traits\MoneyConversion;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MoneyConversionTest extends TestCase
{
    public static function provider()
    {
//        $expected, $value
        return [
            [255, 2.55],
            [0, 0],
            [1, 0.01],
            [-1, -0.01],
            [255, '2.55']
        ];
    }

    #[DataProvider('provider')]
    public function testRoublesToCoins($expected, $value)
    {
        $moneyConversion = new class {
            use MoneyConversion;
        };

        $this->assertEquals($expected, $moneyConversion->roublesToCoins($value));
    }
}
