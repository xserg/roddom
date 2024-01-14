<?php

namespace Tests\Unit\Traits;

use App\Traits\MoneyConversion;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MoneyConversionTest extends TestCase
{
    public static function roublesToCoinsProvider()
    {
//        $expected, $value
        return [
            [255, 2.55],
            [0, 0],
            [1, 0.01],
            [-1, -0.01],
            [255, '2.55'],
            [1500, 15]
        ];
    }

    #[DataProvider('roublesToCoinsProvider')]
    public function testRoublesToCoins($expected, $value)
    {
        $moneyConversion = new class {
            use MoneyConversion;
        };

        $this->assertEquals($expected, $moneyConversion->roublesToCoins($value));
    }

    public static function coinsToRoublesProvider()
    {
//        $expected, $value
        return [
            [2.55, 255],
            [0, 0],
            [0.01, 1],
            [-0.01, -1],
            ['2.55', 255],
            [15, 1500]
        ];
    }

    #[DataProvider('coinsToRoublesProvider')]
    public function testCoinsToRoubles($expected, $value)
    {
        $moneyConversion = new class {
            use MoneyConversion;
        };

        $this->assertEquals($expected, $moneyConversion->coinsToRoubles($value));
    }
}
