<?php

namespace NilzTest\Money\Currency;

use Nilz\Money\Currency\ISO4217Currency;
use PHPUnit\Framework\TestCase;

/**
 * Class CurrencyTest
 *
 * @author Nilz
 */
class CurrencyTest extends TestCase
{
    public function getCurrencyData()
    {
        return [
            ['EUR', 100, 2],
            ['BHD', 1000, 3],
            ['DJF', 1, 0],
        ];
    }

    /**
     * @dataProvider getCurrencyData
     */
    public function testCurrency($alpha3, $factor, $decimalDigits)
    {
        $currency = new ISO4217Currency($alpha3);

        $this->assertSame($alpha3, $currency->getAlpha3());
        $this->assertSame($factor, $currency->getFactorOfSmallestUnit());
        $this->assertSame($decimalDigits, $currency->getDecimalDigits());
    }
}
