<?php

namespace NilzTest\Money;

use InvalidArgumentException;
use Nilz\Money\Currency\Currency;
use Nilz\Money\Currency\ISO4217Currency;
use Nilz\Money\Exception\CurrencyMismatchException;
use Nilz\Money\Money;
use PHPUnit\Framework\TestCase;

/**
 * Class MoneyTest
 *
 * @author Nilz
 */
class MoneyTest extends TestCase
{
    public function getConstructionFailedAmount()
    {
        return [
            'null amount' => [null],
            'float amount' => [0.0],
            'string amount' => ['0.0'],
            'bool amount' => [true],
        ];
    }

    /**
     * @dataProvider getConstructionFailedAmount
     */

    public function getConstructorValidData()
    {
        return [
            'random amount + eur' => [rand(0, 1000), new ISO4217Currency('EUR')],
            'random amount + usd' => [rand(0, 1000), new ISO4217Currency('USD')],
        ];
    }

    /**
     * @dataProvider getConstructorValidData
     */
    public function testConstructorWorksWithInteger($integer, $currency)
    {
        $subject = new Money($integer, $currency);

        $this->assertSame($integer, $subject->getAmount());
        $this->assertSame($currency, $subject->getCurrency());
    }

    public function getAddAndSubtractData()
    {
        return [
            'test add' => [new Money(17, new ISO4217Currency('EUR')), new Money(37, new ISO4217Currency('EUR')), 54, 'add'],
            'test add 2' => [new Money(100, new ISO4217Currency('USD')), new Money(35, new ISO4217Currency('USD')), 135, 'add'],
            'test add negative' => [new Money(-100, new ISO4217Currency('USD')), new Money(35, new ISO4217Currency('USD')), -65, 'add'],
            'test add negative 2' => [new Money(-100, new ISO4217Currency('USD')), new Money(-35, new ISO4217Currency('USD')), -135, 'add'],
            'test subtract' => [new Money(17, new ISO4217Currency('EUR')), new Money(3, new ISO4217Currency('EUR')), 14, 'subtract'],
            'test subtract 2' => [new Money(17, new ISO4217Currency('EUR')), new Money(37, new ISO4217Currency('EUR')), -20, 'subtract'],
            'test subtract negative' => [new Money(-100, new ISO4217Currency('USD')), new Money(-123, new ISO4217Currency('USD')), 23, 'subtract'],
            'test subtract negative 2' => [new Money(-100, new ISO4217Currency('USD')), new Money(5678, new ISO4217Currency('USD')), -5778, 'subtract'],
        ];
    }

    /**
     * @dataProvider getAddAndSubtractData
     */
    public function testAddAndSubtract(Money $argument1, Money $argument2, $expectedAmount, $method)
    {
        $argument1Amount = $argument1->getAmount();
        $argument2Amount = $argument2->getAmount();

        $argument1Currency = $argument1->getCurrency();
        $argument2Currency = $argument2->getCurrency();

        /** @var Money $result */
        $result = $argument1->$method($argument2);

        $this->assertSame($expectedAmount, $result->getAmount());
        $this->assertSame($argument1->getCurrency()->getAlpha3(), $result->getCurrency()->getAlpha3());

        //Test values not being modified
        $this->assertSame($argument1Amount, $argument1->getAmount());
        $this->assertSame($argument1Currency, $argument1->getCurrency());
        $this->assertSame($argument2Amount, $argument2->getAmount());
        $this->assertSame($argument2Currency, $argument2->getCurrency());
    }

    public function getMultiplicationAndDivisionData()
    {
        return [
            'test multiply' => [new Money(17, new ISO4217Currency('EUR')), 2, PHP_ROUND_HALF_UP, 34, 'EUR', 'multiply'],
            'test multiply 2' => [new Money(17, new ISO4217Currency('EUR')), 1.234, PHP_ROUND_HALF_UP, 21, 'EUR', 'multiply'],
            'test multiply negative' => [new Money(17, new ISO4217Currency('USD')), -1.234, PHP_ROUND_HALF_UP, -21, 'USD', 'multiply'],
            'test multiply negative 2' => [new Money(-17, new ISO4217Currency('USD')), -1.234, PHP_ROUND_HALF_UP, 21, 'USD', 'multiply'],
            'test multiply round down' => [new Money(10, new ISO4217Currency('EUR')), 1.449, PHP_ROUND_HALF_UP, 14, 'EUR', 'multiply'],
            'test multiply round up' => [new Money(10, new ISO4217Currency('EUR')), 1.450, PHP_ROUND_HALF_UP, 15, 'EUR', 'multiply'],
            'test multiply round down (half down)' => [new Money(10, new ISO4217Currency('USD')), 1.450, PHP_ROUND_HALF_DOWN, 14, 'USD', 'multiply'],
            'test multiply round up (half down)' => [new Money(10, new ISO4217Currency('USD')), 1.451, PHP_ROUND_HALF_DOWN, 15, 'USD', 'multiply'],
            'test multiply floor' => [new Money(10, new ISO4217Currency('USD')), 1.99, Money::ROUND_DOWN, 19, 'USD', 'multiply'],
            'test multiply ceil' => [new Money(10, new ISO4217Currency('USD')), 1.91, Money::ROUND_UP, 20, 'USD', 'multiply'],
            'test divide' => [new Money(17, new ISO4217Currency('EUR')), 2, PHP_ROUND_HALF_UP, 9, 'EUR', 'divide'],
            'test divide 2' => [new Money(17, new ISO4217Currency('EUR')), 1.234, PHP_ROUND_HALF_UP, 14, 'EUR', 'divide'],
            'test divide negative' => [new Money(17, new ISO4217Currency('USD')), -1.234, PHP_ROUND_HALF_UP, -14, 'USD', 'divide'],
            'test divide negative 2' => [new Money(-17, new ISO4217Currency('USD')), -1.234, PHP_ROUND_HALF_UP, 14, 'USD', 'divide'],
            'test divide round down' => [new Money(1449, new ISO4217Currency('EUR')), 100, PHP_ROUND_HALF_UP, 14, 'EUR', 'divide'],
            'test divide round up' => [new Money(1450, new ISO4217Currency('EUR')), 100, PHP_ROUND_HALF_UP, 15, 'EUR', 'divide'],
            'test divide round down (half down)' => [new Money(1450, new ISO4217Currency('USD')), 100, PHP_ROUND_HALF_DOWN, 14, 'USD', 'divide'],
            'test divide round up (half down)' => [new Money(1451, new ISO4217Currency('USD')), 100, PHP_ROUND_HALF_DOWN, 15, 'USD', 'divide'],
            'test divide floor' => [new Money(1499, new ISO4217Currency('USD')), 100, Money::ROUND_DOWN, 14, 'USD', 'divide'],
            'test divide up' => [new Money(1401, new ISO4217Currency('USD')), 100, Money::ROUND_UP, 15, 'USD', 'divide'],
        ];
    }

    /**
     * @dataProvider getMultiplicationAndDivisionData
     */
    public function testMultiplicationAndDivision(Money $argument1, $argument2, $mode, $expectedResult, $expectedCurrency, $method)
    {
        $argument1Amount = $argument1->getAmount();
        $argument1Currency = $argument1->getCurrency();

        /** @var Money $result */
        $result = $argument1->$method($argument2, $mode);

        $this->assertSame($expectedResult, $result->getAmount());
        $this->assertSame($expectedCurrency, $result->getCurrency()->getAlpha3());

        //Test values not being modified
        $this->assertSame($argument1Amount, $argument1->getAmount());
        $this->assertSame($argument1Currency, $argument1->getCurrency());
    }

    public function getConvertToData()
    {
        return [
            'convert to USD' => [new Money(1451, new ISO4217Currency('EUR')), 1.0567, 1533, 'USD'],
            //Test that factor of currency is adjusted properly
            'convert CLP to EUR' => [new Money(2333443, new ISO4217Currency('CLP')), (1.0 / 747.0), 312375, 'EUR'],
            'convert EUR to CLP' => [new Money(312375, new ISO4217Currency('EUR')), 747.0, 2333441, 'CLP'],
        ];
    }

    /**
     * @dataProvider getConvertToData
     */
    public function testConvertTo(Money $argument1, $argument2, $expectedResult, $expectedCurrency)
    {
        $argument1Amount = $argument1->getAmount();
        $argument1Currency = $argument1->getCurrency();

        /** @var Money $result */
        $result = $argument1->convertTo($argument2, new ISO4217Currency($expectedCurrency));

        $this->assertSame($expectedResult, $result->getAmount());
        $this->assertSame($expectedCurrency, $result->getCurrency()->getAlpha3());

        //Test values not being modified
        $this->assertSame($argument1Amount, $argument1->getAmount());
        $this->assertSame($argument1Currency, $argument1->getCurrency());
    }

    public function getDefaultUnitAmountData()
    {
        return [
            'test euro, 100 factor, 2 digits' => [100, 100, 2, 1.00],
            'test zero digit currency, 1 factor, 0 digits' => [100, 1, 0, 100.0],
            'test 3 digit currency, 1000 factor, 3 digits' => [1000, 1000, 3, 1.000],
            'test factor 100, 3 digits' => [1000, 100, 3, 10.000],
            'test factor 1000, 2 digits' => [1000, 1000, 2, 1.00],
        ];
    }

    /**
     * @dataProvider getDefaultUnitAmountData
     */
    public function testGetDefaultUnitAmount($value, $factor, $digits, $expectedResult)
    {
        $money = new Money($value, new Currency('XXX', $factor, $digits));
        $defaultUnitAmount = $money->getDefaultUnitAmount();

        $this->assertSame($expectedResult, $defaultUnitAmount);
    }

    public function getFormattedAmountData()
    {
        return [
            'test euro, german' => [100, 100, 2, 'EUR', 'de_DE', '1,00 €'],
            'test euro, german bigger value' => [1000000, 100, 2, 'EUR', 'de_DE', '10.000,00 €'],
            'test usd, german' => [100, 100, 2, 'USD', 'de_DE', '1,00 $'],
            'test usd, german bigger value' => [1000000, 100, 2, 'USD', 'de_DE', '10.000,00 $'],
            'test euro' => [100, 100, 2, 'EUR', 'en_US', '€1.00'],
            'test euro, bigger value' => [1000000, 100, 2, 'EUR', 'en_US', '€10,000.00'],
        ];
    }

    /**
     * @dataProvider getFormattedAmountData
     */
    public function testGetFormattedAmount($value, $factor, $digits, $currency, $locale, $expectedResult)
    {
        $money = new Money($value, new Currency($currency, $factor, $digits));
        $defaultUnitAmount = $money->getFormattedAmount($locale);

        $this->assertSame($expectedResult, $defaultUnitAmount);
    }

    public function getCurrencyMismatchData()
    {
        return [
            [new Money(123, new ISO4217Currency('EUR')), new Money(189, new ISO4217Currency('USD'))],
        ];
    }

    public function getFromDefaultUnitData()
    {
        return [
            [123.45, 'EUR', 12345],
            [123.456, 'BHD', 123456],
            [123.456, 'DJF', 123],
        ];
    }

    /**
     * @dataProvider getFromDefaultUnitData
     */
    public function testGetFromDefaultUnit($amount, $currency, $expectedAmount)
    {
        $money = Money::fromDefaultUnitAmount($amount, $currency);

        $this->assertSame($expectedAmount, $money->getAmount());
        $this->assertSame($currency, $money->getCurrency()->getAlpha3());
    }

    public function testCompareTo()
    {
        $money = new Money(123, new ISO4217Currency('EUR'));
        $sameMoney = new Money(123, new ISO4217Currency('EUR'));

        $smallerMoney = new Money(122, new ISO4217Currency('EUR'));
        $biggerMoney = new Money(124, new ISO4217Currency('EUR'));

        $result = $money->compareTo($sameMoney);
        $this->assertSame(0, $result);

        $result = $sameMoney->compareTo($money);
        $this->assertSame(0, $result);

        $result = $money->compareTo($biggerMoney);
        $this->assertLessThan(0, $result);

        $result = $biggerMoney->compareTo($money);
        $this->assertGreaterThan(0, $result);

        $result = $money->compareTo($smallerMoney);
        $this->assertGreaterThan(0, $result);

        $result = $smallerMoney->compareTo($money);
        $this->assertLessThan(0, $result);
    }

    /**
     * @dataProvider getCurrencyMismatchData
     */
    public function testAddWithDifferentCurrenciesFails(Money $summand1, Money $summand2)
    {
        $this->expectException(CurrencyMismatchException::class);

        $summand1->add($summand2);
    }

    /**
     * @dataProvider getCurrencyMismatchData
     */
    public function testSubtractWithDifferentCurrenciesFails(Money $minuend, Money $subtrahend)
    {
        $this->expectException(CurrencyMismatchException::class);

        $minuend->subtract($subtrahend);
    }

    /**
     * @dataProvider getCurrencyMismatchData
     */
    public function testCompareToWithDifferentCurrenciesFails(Money $base, Money $compareObject)
    {
        $this->expectException(CurrencyMismatchException::class);

        $base->compareTo($compareObject);
    }

    public function testPriceInCurrencyExists()
    {
        $money1 = new Money(123, new ISO4217Currency('EUR'));
        $money2 = new Money(50, new ISO4217Currency('EGP'));

        $money1->addCurrencyPrice($money2);

        $result = $money1->getPriceInCurrency('EGP');
        $this->assertSame(50, $result->getAmount());
    }

    public function testPriceInCurrencyNotExists()
    {
        $money1 = new Money(123, new ISO4217Currency('EUR'));
        $money2 = new Money(50, new ISO4217Currency('USD'));

        $money1->addCurrencyPrice($money2);

        // here we will get the default price if the Currency not exists
        $result = $money1->getPriceInCurrency('EGP', true);
        $this->assertSame(123, $result->getAmount());
    }
}
