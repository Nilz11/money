<?php

namespace NilzTest\Money\Currency;

use Nilz\Money\Currency\Currency;
use PHPUnit\Framework\TestCase;

/**
 * Class ISO4217CurrencyTest
 *
 * @author Nilz
 */
class ISO4217CurrencyTest extends TestCase
{
    public function testCurrency()
    {
        $alpha3 = (string)rand(0, 1000);
        $factor = (string)rand(0, 1000);
        $decimalDigits = (string)rand(0, 1000);

        $currency = new Currency($alpha3, $factor, $decimalDigits);

        $this->assertSame($alpha3, $currency->getAlpha3());
        $this->assertSame($factor, $currency->getFactorOfSmallestUnit());
        $this->assertSame($decimalDigits, $currency->getDecimalDigits());
    }
}
