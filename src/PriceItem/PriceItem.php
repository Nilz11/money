<?php

namespace Nilz\Money\PriceItem;

use Nilz\Money\PriceTag\CurrencyPriceTag;

/**
 * Class PriceItem
 *
 * A price item can be thought of as a product row of an order.
 *
 * @author Nilz
 */
class PriceItem
{
    /**
     * Buy price of the product
     * @var CurrencyPriceTag
     */
    protected $basePrice;

    /**
     * Factor
     * @var float
     */
    protected $factor;

    /**
     * Internal retail price
     * @var CurrencyPriceTag
     */
    protected $retailPrice;

    /**
     * Additional costs
     * @var CurrencyPriceTag
     */
    protected $additionalCosts;

    /**
     * Unit price / external retail price
     * @var CurrencyPriceTag
     */
    protected $unitPrice;

    /**
     * Amount
     * @var integer
     */
    protected $amount;

    /**
     * Row price
     * @var CurrencyPriceTag
     */
    protected $itemPrice;

    /**
     * @param CurrencyPriceTag $basePrice
     * @param float            $factor
     * @param CurrencyPriceTag $retailPrice
     * @param CurrencyPriceTag $additionalCosts
     * @param CurrencyPriceTag $unitPrice
     * @param int              $amount
     * @param CurrencyPriceTag $itemPrice
     */
    public function __construct(CurrencyPriceTag $basePrice, $factor, CurrencyPriceTag $retailPrice, CurrencyPriceTag $additionalCosts, CurrencyPriceTag $unitPrice, $amount, CurrencyPriceTag $itemPrice)
    {
        $this->basePrice = $basePrice;
        $this->factor = $factor;
        $this->retailPrice = $retailPrice;
        $this->additionalCosts = $additionalCosts;
        $this->unitPrice = $unitPrice;
        $this->amount = $amount;
        $this->itemPrice = $itemPrice;
    }

    /**
     * {@inheritdoc}
     */
    public function getBasePrice()
    {
        return $this->basePrice;
    }

    /**
     * {@inheritdoc}
     */
    public function getFactor()
    {
        return $this->factor;
    }

    /**
     * {@inheritdoc}
     */
    public function getRetailPrice()
    {
        return $this->retailPrice;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdditionalCosts()
    {
        return $this->additionalCosts;
    }

    /**
     * {@inheritdoc}
     */
    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    /**
     * {@inheritdoc}
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * {@inheritdoc}
     */
    public function getItemPrice()
    {
        return $this->itemPrice;
    }
}
