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
     * @inheritdoc
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultUnitAmount()
    {
        return round($this->amount / $this->currency->getFactorOfSmallestUnit(), $this->currency->getDecimalDigits());
    }

    /**
     * @inheritdoc
     */
    public function getFormattedAmount($locale = null, $hideFractionDigits = false)
    {
        $convertedAmount = $this->getDefaultUnitAmount();
        $formatter = $this->getCurrencyFormatter($locale);

        if ($hideFractionDigits) {
            //We dont display cents if there arent any. Php does not support that out of box, because we dont want numbers like 820,1€
            if ($this->getAmount() % 100 === 0) {
                $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 0);
            } else {
                $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 2);
            }
        }

        return $formatter->formatCurrency($convertedAmount, $this->getCurrency()->getAlpha3());
    }

    /**
     * @inheritdoc
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @inheritdoc
     */
    public function add(Money $summand)
    {
        $this->assertSameCurrency($summand);

        $sum = $this->amount + $summand->getAmount();

        return $this->newMoney($sum);
    }

    /**
     * @inheritdoc
     */
    public function subtract(Money $subtrahend)
    {
        $this->assertSameCurrency($subtrahend);

        $difference = $this->amount - $subtrahend->getAmount();

        return $this->newMoney($difference);
    }

    /**
     * @inheritdoc
     */
    public function multiply($factor, $mode = PHP_ROUND_HALF_UP)
    {
        return $this->convertTo($factor, $this->currency, $mode);
    }

    /**
     * @inheritdoc
     */
    public function divide($divisor, $mode = PHP_ROUND_HALF_UP)
    {
        $quotient = $this->round($this->amount / $divisor, $mode);

        return $this->newMoney($quotient);
    }

    /**
     * @inheritdoc
     */
    public function convertTo($ratio, CurrencyInterface $currency, $mode = PHP_ROUND_HALF_UP)
    {
        $currencyFactor = $currency->getFactorOfSmallestUnit() / $this->currency->getFactorOfSmallestUnit();

        $product = $this->round($this->amount * $ratio * $currencyFactor, $mode);

        return new static($product, $currency);
    }

    /**
     * @inheritdoc
     */
    public function compareTo(Money $money)
    {
        $this->assertSameCurrency($money);

        return $this->amount - $money->getAmount();
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function assertSameCurrency(Money $money)
    {
        if (!$this->isSameCurrency($money)) {
            throw new CurrencyMismatchException(sprintf('Currency %s does not match %s of other object.', $this->getCurrency()->getAlpha3(), $money->getCurrency()->getAlpha3()));
        }
    }

    /**
     * @inheritdoc
     */
    public function isSameCurrency(Money $money)
    {
        if ($this->getCurrency()->getAlpha3() !== $money->getCurrency()->getAlpha3()) {
            return false;
        }
        return true;
    }
}
