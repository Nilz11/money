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
    protected $basePriceTag;

    /**
     * Display price tag in display currency
     * @var PriceTag
     */
    protected $displayPriceTag;

    /**
     * Exchange rate between base price tag and display price tag
     * @var float
     */
    protected $exchangeRate;

    /**
     * @param PriceTag $basePriceTag
     * @param PriceTag $displayPriceTag
     * @param integer  $exchangeRate
     */
    public function __construct(PriceTag $basePriceTag, PriceTag $displayPriceTag, $exchangeRate)
    {
        $basePriceTag->assertTaxPercentage($displayPriceTag);

        $this->basePriceTag = $basePriceTag;
        $this->displayPriceTag = $displayPriceTag;

        $this->exchangeRate = $exchangeRate;
    }

    /**
     * @return PriceTag
     */
    public function getBasePriceTag()
    {
        return $this->basePriceTag;
    }

    /**
     * @return PriceTag
     */
    public function getDisplayPriceTag()
    {
        return $this->displayPriceTag;
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

        $basePriceTag = $this->getBasePriceTag()->add($summand->getBasePriceTag());

        $displayPriceTag = $this->getDisplayPriceTag()->add($summand->getDisplayPriceTag());

        return $this->newCurrencyPriceTag($basePriceTag, $displayPriceTag);
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

        $basePriceTag = $this->getBasePriceTag()->subtract($subtrahend->getBasePriceTag());

        $displayPriceTag = $this->getDisplayPriceTag()->subtract($subtrahend->getDisplayPriceTag());

        return $this->newCurrencyPriceTag($basePriceTag, $displayPriceTag);
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
        $basePriceTag = $this->getBasePriceTag()->multiply($factor, $mode);

        $displayPriceTag = $this->getBasePriceTag()->multiply($factor, $mode);

        return $this->newCurrencyPriceTag($basePriceTag, $displayPriceTag);
    }

    /**
     * Divides currency price tag
     *
     * @param float $divisor
     * @param int   $mode
     *
     * @return CurrencyPriceTag
     */
    public function divice($divisor, $mode = PHP_ROUND_HALF_UP)
    {
        $basePriceTag = $this->getBasePriceTag()->divide($divisor, $mode);

        $displayPriceTag = $this->getBasePriceTag()->divide($divisor, $mode);

        return $this->newCurrencyPriceTag($basePriceTag, $displayPriceTag);
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
     * @param PriceTag $basePriceTag
     * @param PriceTag $displayPriceTag
     *
     * @return static
     */
    public function newCurrencyPriceTag(PriceTag $basePriceTag, PriceTag $displayPriceTag)
    {
        return new static($basePriceTag, $displayPriceTag, $this->exchangeRate);
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
