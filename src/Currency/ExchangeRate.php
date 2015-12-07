<?php

namespace Nilz\Money\Currency;

class ExchangeRate
{
    /**
     * @var CurrencyInterface
     */
    protected $currency;

    /**
     * @var float
     */
    protected $exchangeRate;

    public function __construct(CurrencyInterface $currency, $exchangeRate)
    {
        $this->currency = $currency;
        $this->exchangeRate = $exchangeRate;
    }

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return mixed
     */
    public function getExchangeRate()
    {
        return $this->exchangeRate;
    }
}
