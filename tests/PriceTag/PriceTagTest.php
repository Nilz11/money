<?php

namespace NilzTest\Money\PriceTag;

use Nilz\Money\Currency\ISO4217Currency;
use Nilz\Money\Exception\CurrencyMismatchException;
use Nilz\Money\Exception\TaxMismatchException;
use Nilz\Money\Money;
use Nilz\Money\PriceTag\PriceTag;
use PHPUnit\Framework\TestCase;

class PriceTagTest extends TestCase
{

    public function getConstructor()
    {
        return [
            [
                new Money(1234, new ISO4217Currency('EUR')),
                new Money(2345, new ISO4217Currency('EUR')),
                19.5,
                1111
            ],
            [
                new Money(2345, new ISO4217Currency('EUR')),
                new Money(1234, new ISO4217Currency('EUR')),
                1234.5,
                -1111
            ]
        ];
    }

    /**
     * @dataProvider getConstructor
     */
    public function testConstructorAndGetters(Money $netPrice, Money $grossPrice, $taxPercentage, $taxValue)
    {
        $subject = new PriceTag($netPrice, $grossPrice, $taxPercentage);

        $this->assertSame($netPrice, $subject->getNetPrice());
        $this->assertSame($grossPrice, $subject->getGrossPrice());
        $this->assertSame($taxPercentage, $subject->getTaxPercentage());

        $this->assertInstanceOf('Nilz\Money\Money', $subject->getTaxValue());
        $this->assertSame($taxValue, $subject->getTaxValue()->getAmount());
        $this->assertSame($netPrice->getCurrency()->getAlpha3(), $subject->getTaxValue()->getCurrency()->getAlpha3());
    }

    public function getConstructorFailsWithDifferentCurrencies()
    {
        return [
            [
                new Money(1234, new ISO4217Currency('EUR')),
                new Money(2345, new ISO4217Currency('USD')),
            ],
        ];
    }

    /**
     * @dataProvider getConstructorFailsWithDifferentCurrencies
     */
    public function testConstructorFailsWithDifferentCurrencies(Money $netPrice, Money $grossPrice)
    {
        $this->expectException(CurrencyMismatchException::class);

        new PriceTag($netPrice, $grossPrice, 1234);
    }

    public function getMethodFailsWithDifferentCurrencies()
    {
        $money1 = new Money(1234, new ISO4217Currency('EUR'));
        $money2 = new Money(1234, new ISO4217Currency('USD'));

        $priceTag1 = new PriceTag($money1, $money1, 1234);
        $priceTag2 = new PriceTag($money2, $money2, 1234);

        return [
            [$priceTag1, $priceTag2, 'add'],
            [$priceTag1, $priceTag2, 'subtract'],
            [$priceTag1, $priceTag2, 'compareTo'],
        ];
    }

    /**
     * @dataProvider getMethodFailsWithDifferentCurrencies
     */
    public function testMethodFailsWithDifferentCurrencies(PriceTag $priceTag, PriceTag $priceTag2, $method)
    {
        $this->expectException(CurrencyMismatchException::class);

        $priceTag->$method($priceTag2);
    }

    public function getMethodFailsWithDifferentTaxPercentages()
    {
        $money1 = new Money(1234, new ISO4217Currency('EUR'));
        $money2 = new Money(1234, new ISO4217Currency('EUR'));

        $priceTag1 = new PriceTag($money1, $money1, 123);
        $priceTag2 = new PriceTag($money2, $money2, 234);

        return [
            [$priceTag1, $priceTag2, 'add'],
            [$priceTag1, $priceTag2, 'subtract'],
            [$priceTag1, $priceTag2, 'compareTo'],
        ];
    }

    /**
     * @dataProvider getMethodFailsWithDifferentTaxPercentages
     */
    public function testMethodFailsWithDifferentTaxPercentages(PriceTag $priceTag, PriceTag $priceTag2, $method)
    {
        $this->expectException(TaxMismatchException::class);

        $priceTag->$method($priceTag2);
    }

    protected function getPriceTag($money1, $money2, $currency = 'EUR')
    {
        $money1 = new Money($money1, new ISO4217Currency($currency));
        $money2 = new Money($money2, new ISO4217Currency($currency));

        return new PriceTag($money1, $money2, 19);
    }

    public function getAddAndSubtract()
    {
        $random1 = rand(-10000, 10000);
        $random2 = rand(-10000, 10000);
        $random3 = rand(-10000, 10000);
        $random4 = rand(-10000, 10000);

        $sum1 = $random1 + $random3;
        $sum2 = $random2 + $random4;

        $dividend1 = $random1 - $random3;
        $dividend2 = $random2 - $random4;

        return [
            'test add' => [$this->getPriceTag(1235, 1470), $this->getPriceTag(1267, 1508), 'add', 2502, 2978],
            'test add negative' => [$this->getPriceTag(-1235, -1470), $this->getPriceTag(-1267, 1508), 'add', -2502, 38],
            'test add random' => [$this->getPriceTag($random1, $random2), $this->getPriceTag($random3, $random4), 'add', $sum1, $sum2],
            'test subtract' => [$this->getPriceTag(1235, 1470), $this->getPriceTag(1267, 1508), 'subtract', -32, -38],
            'test subtract negative' => [$this->getPriceTag(-1235, -1470), $this->getPriceTag(-1267, 1508), 'subtract', 32, -2978],
            'test subtract random' => [$this->getPriceTag($random1, $random2), $this->getPriceTag($random3, $random4), 'subtract', $dividend1, $dividend2],
        ];
    }

    /**
     * @dataProvider getAddAndSubtract
     */
    public function testAddAndSubtract(PriceTag $priceTag, PriceTag $priceTag2, $method, $expNet, $expGross)
    {
        /** @var PriceTag $return */
        $return = $priceTag->$method($priceTag2);

        $this->assertInstanceOf('Nilz\Money\PriceTag\PriceTag', $return);

        $this->assertSame($expNet, $return->getNetPrice()->getAmount());
        $this->assertSame($expGross, $return->getGrossPrice()->getAmount());

        $this->assertSame($priceTag->getNetPrice()->getCurrency()->getAlpha3(), $return->getNetPrice()->getCurrency()->getAlpha3());
        $this->assertSame($priceTag->getNetPrice()->getCurrency()->getAlpha3(), $return->getGrossPrice()->getCurrency()->getAlpha3());

        $this->assertSame($priceTag->getTaxPercentage(), $return->getTaxPercentage());
    }

    public function getMultiplyAndDivide()
    {
        $random1 = rand(-10000, 10000);
        $random2 = rand(-10000, 10000);
        $random3 = rand(-10000, 10000);

        $mul1 = (int)round($random1 * $random3);
        $mul2 = (int)round($random2 * $random3);

        $division1 = (int)round($random1 / $random3);
        $division2 = (int)round($random2 / $random3);

        return [
            'test multiply' => [$this->getPriceTag(1235, 1470), 1.2345, 'multiply', 1525, 1815],
            'test multiply negative' => [$this->getPriceTag(-1235, -1470), 1.2345, 'multiply', -1525, -1815],
            'test multiply random' => [$this->getPriceTag($random1, $random2), $random3, 'multiply', $mul1, $mul2],
            'test divide' => [$this->getPriceTag(1235, 1470), 1.2345, 'divide', 1000, 1191],
            'test divide negative' => [$this->getPriceTag(-1235, -1470), 1.2345, 'divide', -1000, -1191],
            'test divide random' => [$this->getPriceTag($random1, $random2), $random3, 'divide', $division1, $division2],
        ];
    }

    /**
     * @dataProvider getMultiplyAndDivide
     */
    public function testMultiplyAndDivide(PriceTag $priceTag, $factor, $method, $expNet, $expGross)
    {
        /** @var PriceTag $return */
        $return = $priceTag->$method($factor);

        $this->assertInstanceOf('Nilz\Money\PriceTag\PriceTag', $return);

        $this->assertSame($expNet, $return->getNetPrice()->getAmount());
        $this->assertSame($expGross, $return->getGrossPrice()->getAmount());

        $this->assertSame($priceTag->getNetPrice()->getCurrency()->getAlpha3(), $return->getNetPrice()->getCurrency()->getAlpha3());
        $this->assertSame($priceTag->getNetPrice()->getCurrency()->getAlpha3(), $return->getGrossPrice()->getCurrency()->getAlpha3());

        $this->assertSame($priceTag->getTaxPercentage(), $return->getTaxPercentage());
    }

    public function getConvertTo()
    {
        $random1 = rand(-10000, 10000);
        $random2 = rand(-10000, 10000);
        $random3 = rand(-10000, 10000);

        $mul1 = (int)round($random1 * $random3);
        $mul2 = (int)round($random2 * $random3);

        return [
            'test convert to' => [$this->getPriceTag(1235, 1470), 1.2345, 1525, 1815, 'USD'],
            'test convert to negative' => [$this->getPriceTag(-1235, -1470), 1.2345, -1525, -1815, 'EUR'],
            'test convert to random' => [$this->getPriceTag($random1, $random2), $random3, $mul1, $mul2, 'USD'],
        ];
    }

    /**
     * @dataProvider getConvertTo
     */
    public function testConvertTo(PriceTag $priceTag, $factor, $expNet, $expGross, $expCurrency)
    {
        /** @var PriceTag $return */
        $return = $priceTag->convertTo($factor, new ISO4217Currency($expCurrency));

        $this->assertInstanceOf('Nilz\Money\PriceTag\PriceTag', $return);

        $this->assertSame($expNet, $return->getNetPrice()->getAmount());
        $this->assertSame($expGross, $return->getGrossPrice()->getAmount());

        $this->assertSame($expCurrency, $return->getNetPrice()->getCurrency()->getAlpha3());
        $this->assertSame($expCurrency, $return->getGrossPrice()->getCurrency()->getAlpha3());

        $this->assertSame($priceTag->getTaxPercentage(), $return->getTaxPercentage());
    }
}
