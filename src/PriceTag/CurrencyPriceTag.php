<?php

namespace Nilz\Money\PriceTag;

use Nilz\Money\Currency\ExchangeRate;
use Nilz\Money\MoneyInterface;

/**
 * Class CurrencyPriceTag
 * @author Nilz
 */
class CurrencyPriceTag extends PriceTag
{
    /**
     * @var ExchangeRate
     */
    protected $exchangeRate;

    /**
     * @param MoneyInterface $netPrice
     * @param MoneyInterface $grossPrice
     * @param float          $taxPercentage
     * @param ExchangeRate   $exchangeRate
     */
    public function __construct(MoneyInterface $netPrice, MoneyInterface $grossPrice, $taxPercentage, ExchangeRate $exchangeRate)
    {
        parent::__construct($netPrice, $grossPrice, $taxPercentage);

        $this->exchangeRate = $exchangeRate;
    }

    /**
     * @return ExchangeRate
     */
    public function getExchangeRate()
    {
        return $this->exchangeRate;
    }
}
