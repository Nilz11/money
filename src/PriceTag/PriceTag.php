<?php

namespace Nilz\Money\PriceTag;

use Nilz\Money\Currency\CurrencyInterface;
use Nilz\Money\Exception\TaxMismatchException;
use Nilz\Money\Money;

/**
 * Class PriceTag
 * A price tag is a combination of net and gross price. The property tax percentage indicates the tax between net and gross price.
 * If no tax is needed, put tax percentage to 0.0 or use money object directly.
 * @author Nilz
 */
class PriceTag
{
    /**
     * Net price
     * @var Money
     */
    protected $netPrice;

    /**
     * Gross price
     * @var Money
     */
    protected $grossPrice;

    /**
     * Tax percentage between net and gross price, e.g 19.5 (percent)
     * @var float
     */
    protected $taxPercentage;

    /**
     * @param Money $netPrice
     * @param Money $grossPrice
     * @param float          $taxPercentage
     */
    public function __construct(Money $netPrice, Money $grossPrice, $taxPercentage)
    {
        $netPrice->assertSameCurrency($grossPrice);

        $this->netPrice = $netPrice;
        $this->grossPrice = $grossPrice;

        $this->taxPercentage = $taxPercentage;
    }

    /**
     * Gets net price
     * @return Money
     */
    public function getNetPrice()
    {
        return $this->netPrice;
    }

    /**
     * Gets gross price
     * @return Money
     */
    public function getGrossPrice()
    {
        return $this->grossPrice;
    }

    /**
     * Gets tax value between net and gross price
     * @return Money
     */
    public function getTaxValue()
    {
        return $this->grossPrice->subtract($this->netPrice);
    }

    /**
     * Gets tax percentage between net and gross price, e.g 19.5 (percent)
     * @return float
     */
    public function getTaxPercentage()
    {
        return $this->taxPercentage;
    }

    /**
     * Adds price tag to object
     *
     * @param PriceTag $summand
     *
     * @return PriceTag
     */
    public function add(PriceTag $summand)
    {
        $this->assertTaxPercentage($summand);

        $netPrice = $this->getNetPrice()->add($summand->getNetPrice());

        $grossPrice = $this->getGrossPrice()->add($summand->getGrossPrice());

        return $this->newPriceTag($netPrice, $grossPrice);
    }

    /**
     * Subtracts price tag to object
     *
     * @param PriceTag $subtrahend
     *
     * @return PriceTag
     */
    public function subtract(PriceTag $subtrahend)
    {
        $this->assertTaxPercentage($subtrahend);

        $netPrice = $this->getNetPrice()->subtract($subtrahend->getNetPrice());

        $grossPrice = $this->getGrossPrice()->subtract($subtrahend->getGrossPrice());

        return $this->newPriceTag($netPrice, $grossPrice);
    }

    /**
     * Multiplies price tag by given factor
     *
     * @param float   $factor
     * @param integer $mode
     *
     * @return PriceTag
     */
    public function multiply($factor, $mode = PHP_ROUND_HALF_UP)
    {
        $netPrice = $this->getNetPrice()->multiply($factor, $mode);

        $grossPrice = $this->getGrossPrice()->multiply($factor, $mode);

        return $this->newPriceTag($netPrice, $grossPrice);
    }

    /**
     * Divides price tag by given divisor
     *
     * @param float   $factor
     * @param integer $mode
     *
     * @return PriceTag
     */
    public function divide($factor, $mode = PHP_ROUND_HALF_UP)
    {
        $netPrice = $this->getNetPrice()->divide($factor, $mode);

        $grossPrice = $this->getGrossPrice()->divide($factor, $mode);

        return $this->newPriceTag($netPrice, $grossPrice);
    }

    /**
     * Converts price tag to currency
     *
     * @param float             $ratio
     * @param CurrencyInterface $currency
     * @param int               $mode
     *
     * @return PriceTag
     */
    public function convertTo($ratio, CurrencyInterface $currency, $mode = PHP_ROUND_HALF_UP)
    {
        $netPrice = $this->getNetPrice()->convertTo($ratio, $currency, $mode);

        $grossPrice = $this->getGrossPrice()->convertTo($ratio, $currency, $mode);

        return $this->newPriceTag($netPrice, $grossPrice);
    }

    /**
     * Compares price tag
     *
     * @param PriceTag $priceTag
     *
     * @return int
     */
    public function compareTo(PriceTag $priceTag)
    {
        $this->assertTaxPercentage($priceTag);

        return $this->getNetPrice()->compareTo($priceTag->getNetPrice());
    }

    /**
     * Returns new price tag with net and gross price but keeps tax percentage
     *
     * @param Money $netPrice
     * @param Money $grossPrice
     *
     * @return static
     */
    public function newPriceTag(Money $netPrice, Money $grossPrice)
    {
        return new static($netPrice, $grossPrice, $this->taxPercentage);
    }

    /**
     * Asserts equality of tax percentages
     *
     * @param PriceTag $priceTag
     *
     * @throws TaxMismatchException
     */
    public function assertTaxPercentage(PriceTag $priceTag)
    {
        if ($this->taxPercentage != $priceTag->getTaxPercentage()) {
            throw new TaxMismatchException(sprintf('Tax percentage %i must match %i when adding or subtracting price tags', $this->taxPercentage, $priceTag->getTaxPercentage()));
        }
    }
}
