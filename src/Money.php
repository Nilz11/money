<?php

namespace Nilz\Money;

use InvalidArgumentException;
use Locale;
use NumberFormatter;
use Nilz\Money\Currency\CurrencyInterface;
use Nilz\Money\Currency\ISO4217Currency;
use Nilz\Money\Exception\CurrencyMismatchException;

/**
 * Class Money
 *
 * @author Nilz
 */
class Money
{
    /**
     * @var integer
     */
    protected $amount;

    /**
     * @var CurrencyInterface
     */
    protected $currency;

    /**
     * @param integer           $amount
     * @param CurrencyInterface $currency
     */
    public function __construct($amount, CurrencyInterface $currency)
    {
        if (!is_int($amount)) {
            throw new InvalidArgumentException("Money $amount must be valid integer");
        }

        $this->amount = $amount;
        $this->currency = $currency;
    }

    /**
     * Convenience method to get money object using float amount and currency alpha 3 string
     *
     * @param float   $amount   Converted amount, e.g. 4.20
     * @param string  $currency Alpha 3 string for currency, e.g. EUR
     * @param integer $mode     Rounding mode
     *
     * @return Money
     */
    public static function fromDefaultUnitAmount($amount, $currency, $mode = PHP_ROUND_HALF_UP)
    {
        $currency = new ISO4217Currency($currency);

        $amount = (int)round($amount * $currency->getFactorOfSmallestUnit(), 0, $mode);

        return new Money($amount, $currency);
    }

    /**
     * Gets raw amount in smallest unit representation of currency, e.g. 420 (eurocents)
     *
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Gets amount in the default unit representation of the currency, e.g. euros instead of eurocents
     *
     * @return float
     */
    public function getDefaultUnitAmount()
    {
        return round($this->amount / $this->currency->getFactorOfSmallestUnit(), $this->currency->getDecimalDigits());
    }

    /**
     * Returns formatted amount using the given locale. Uses number formatter of php intl extension, e.g. 4,20 €
     *
     * @param null|string $locale e.g. en_CA, defaults to Locale::getDefault()
     *
     * @return string
     */
    public function getFormattedAmount($locale = null)
    {
        $convertedAmount = $this->getDefaultUnitAmount();

        return $this->getCurrencyFormatter($locale)->formatCurrency($convertedAmount, $this->getCurrency()->getAlpha3());
    }

    /**
     * Gets currency of money object
     * @return CurrencyInterface
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Adds money to the money object
     *
     * @param Money $summand
     *
     * @return Money
     */
    public function add(Money $summand)
    {
        $this->assertSameCurrency($summand);

        $sum = $this->amount + $summand->getAmount();

        return $this->newMoney($sum);
    }

    /**
     * Subtracts money from money object
     *
     * @param Money $subtrahend
     *
     * @return Money
     */
    public function subtract(Money $subtrahend)
    {
        $this->assertSameCurrency($subtrahend);

        $difference = $this->amount - $subtrahend->getAmount();

        return $this->newMoney($difference);
    }

    /**
     * Multiplies money object by given factor
     *
     * @param float   $factor
     * @param integer $mode
     *
     * @return Money
     */
    public function multiply($factor, $mode = PHP_ROUND_HALF_UP)
    {
        return $this->convertTo($factor, $this->currency, $mode);
    }

    /**
     * Divides money object by given divisor
     *
     * @param float   $divisor
     * @param integer $mode
     *
     * @return Money
     */
    public function divide($divisor, $mode = PHP_ROUND_HALF_UP)
    {
        $quotient = $this->round($this->amount / $divisor, $mode);

        return $this->newMoney($quotient);
    }

    /**
     * Converts money object to object with different currency
     *
     * @param integer           $ratio
     * @param CurrencyInterface $currency
     * @param integer           $mode
     *
     * @return static
     */
    public function convertTo($ratio, $currency, $mode = PHP_ROUND_HALF_UP)
    {
        $product = $this->round($this->amount * $ratio, $mode);

        return new static($product, $currency);
    }

    /**
     * Compares money object to given money object
     * Returns < 0: Amount of object is smaller than given object
     * Returns 0: Amount is the same
     * Return > 0: Amount of object is bigger than given object
     *
     * @param Money $money
     *
     * @return integer -|0|+
     */
    public function compareTo(Money $money)
    {
        $this->assertSameCurrency($money);

        return $this->amount - $money->getAmount();
    }

    /**
     * Returns new money object with same currency but given amount
     *
     * @param integer $amount
     *
     * @return Money
     */
    public function newMoney($amount)
    {
        return new static($amount, $this->currency);
    }

    /**
     * @param integer $amount
     * @param integer $mode
     *
     * @return float
     */
    protected function round($amount, $mode = PHP_ROUND_HALF_UP)
    {
        return (int)round($amount, 0, $mode);
    }

    /**
     * Returns number formatter class using the given locale
     *
     * @param null|string $locale e.g. en_CA, defaults to Locale::getDefault()
     *
     * @return NumberFormatter
     */
    protected function getCurrencyFormatter($locale = null)
    {
        $locale = $locale ?: Locale::getDefault();

        return NumberFormatter::create($locale, NumberFormatter::CURRENCY);
    }

    /**
     * Asserts if the given money object has the same currency as the object itself
     *
     * @param Money $money
     *
     * @throws CurrencyMismatchException
     */
    public function assertSameCurrency(Money $money)
    {
        if (!$this->isSameCurrency($money)) {
            throw new CurrencyMismatchException(sprintf('Currency %s does not match %s of other object.', $this->getCurrency()->getAlpha3(), $money->getCurrency()->getAlpha3()));
        }
    }

    /**
     * If the currency is the same as the currency of money
     *
     * @param Money $money
     *
     * @return bool
     */
    public function isSameCurrency(Money $money)
    {
        if ($this->getCurrency()->getAlpha3() !== $money->getCurrency()->getAlpha3()) {
            return false;
        }
        return true;
    }
}
