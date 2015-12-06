<?php

namespace Nilz\Money\PriceTag;

use Nilz\Money\Exception\ExchangeRateMismatchException;

/**
 * Class CurrencyPriceTag
 *
 * Implementation of price tag containing two currencies. It's an implementation I needed for another project.
 *
 * @author Nilz
 */
class CurrencyPriceTag
{
    /**
     * Base price tag in base currency
     * @var PriceTag
     */
    protected $baseCurrency;

    /**
     * Display price tag in display currency
     * @var PriceTag
     */
    protected $displayCurrency;

    /**
     * Exchange rate between base price tag and display price tag
     * @var float
     */
    protected $exchangeRate;

    /**
     * @param PriceTag $baseCurrency
     * @param PriceTag $displayCurrency
     * @param integer  $exchangeRate
     */
    public function __construct(PriceTag $baseCurrency, PriceTag $displayCurrency, $exchangeRate)
    {
        $baseCurrency->assertTaxPercentage($displayCurrency);

        $this->baseCurrency = $baseCurrency;
        $this->displayCurrency = $displayCurrency;

        $this->exchangeRate = $exchangeRate;
    }

    /**
     * @return PriceTag
     */
    public function getBaseCurrency()
    {
        return $this->baseCurrency;
    }

    /**
     * @return PriceTag
     */
    public function getDisplayCurrency()
    {
        return $this->displayCurrency;
    }

    /**
     * @return float
     */
    public function getExchangeRate()
    {
        return $this->exchangeRate;
    }

    /**
     * Adds currency price tag
     *
     * @param CurrencyPriceTag $summand
     *
     * @return CurrencyPriceTag
     */
    public function add(CurrencyPriceTag $summand)
    {
        $this->assertExchangeRate($summand);

        $baseCurrency = $this->getBaseCurrency()->add($summand->getBaseCurrency());

        $displayCurrency = $this->getDisplayCurrency()->add($summand->getdisplayCurrency());

        return $this->newCurrencyPriceTag($baseCurrency, $displayCurrency);
    }

    /**
     * Subtracts currency price tag
     *
     * @param CurrencyPriceTag $subtrahend
     *
     * @return CurrencyPriceTag
     */
    public function subtract(CurrencyPriceTag $subtrahend)
    {
        $this->assertExchangeRate($subtrahend);

        $baseCurrency = $this->getBaseCurrency()->subtract($subtrahend->getBaseCurrency());

        $displayCurrency = $this->getDisplayCurrency()->subtract($subtrahend->getdisplayCurrency());

        return $this->newCurrencyPriceTag($baseCurrency, $displayCurrency);
    }

    /**
     * Multiplies currency price tag
     *
     * @param float $factor
     * @param int   $mode
     *
     * @return CurrencyPriceTag
     */
    public function multiply($factor, $mode = PHP_ROUND_HALF_UP)
    {
        $baseCurrency = $this->getBaseCurrency()->multiply($factor, $mode);

        $displayCurrency = $this->getDisplayCurrency()->multiply($factor, $mode);

        return $this->newCurrencyPriceTag($baseCurrency, $displayCurrency);
    }

    /**
     * Divides currency price tag
     *
     * @param float $divisor
     * @param int   $mode
     *
     * @return CurrencyPriceTag
     */
    public function divide($divisor, $mode = PHP_ROUND_HALF_UP)
    {
        $baseCurrency = $this->getBaseCurrency()->divide($divisor, $mode);

        $displayCurrency = $this->getDisplayCurrency()->divide($divisor, $mode);

        return $this->newCurrencyPriceTag($baseCurrency, $displayCurrency);
    }

    /**
     * @param float $taxPercentage
     *
     * @return float
     */
    protected function percentageToFactor($taxPercentage)
    {
        return $taxPercentage / 100;
    }

    /**
     * Returns new static of currency price tag but with same exchange rate
     *
     * @param PriceTag $baseCurrency
     * @param PriceTag $displayCurrency
     *
     * @return static
     */
    public function newCurrencyPriceTag(PriceTag $baseCurrency, PriceTag $displayCurrency)
    {
        return new static($baseCurrency, $displayCurrency, $this->exchangeRate);
    }

    /**
     * Asserts equality of exchange rate
     *
     * @param CurrencyPriceTag $currencyPriceTag
     *
     * @throws ExchangeRateMismatchException
     */
    public function assertExchangeRate(CurrencyPriceTag $currencyPriceTag)
    {
        if ($this->exchangeRate !== $currencyPriceTag->getExchangeRate()) {
            throw new ExchangeRateMismatchException(sprintf('Exchange rate %i must match %i when adding or subtracting currency price tags', $this->exchangeRate, $currencyPriceTag->getExchangeRate()));
        }
    }
}
