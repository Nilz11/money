<?php

namespace Nilz\Money\Currency;

/**
 * Interface CurrencyInterface
 */
interface CurrencyInterface
{
    /**
     * Gets iso 4217 alpha 3 code of the currency
     *
     * @return string
     */
    public function getAlpha3();

    /**
     * Gets factor of smallest unit to default unit, e.g. 100 for euro to eurocents
     *
     * @return integer
     */
    public function getFactorOfSmallestUnit();

    /**
     * Gets number of decimal digits for currency in default representation
     *
     * @return integer
     */
    public function getDecimalDigits();
}
