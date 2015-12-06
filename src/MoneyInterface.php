<?php

namespace Nilz\Money;

use Nilz\Money\Currency\CurrencyInterface;
use Nilz\Money\Exception\CurrencyMismatchException;

/**
 * Interface MoneyInterface
 *
 * @author Nilz
 */
interface MoneyInterface
{
    /**
     * Gets raw amount in smallest unit representation of currency, e.g. 420 (eurocents)
     * @return int
     */
    public function getAmount();

    /**
     * Gets amount in the default unit representation of the currency, e.g. euros instead of eurocents
     * @return float
     */
    public function getDefaultUnitAmount();

    /**
     * Returns formatted amount using the given locale. Uses number formatter of php intl extension, e.g. 4,20 €
     *
     * @param null|string $locale e.g. en_CA, defaults to Locale::getDefault()
     *
     * @return string
     */
    public function getFormattedAmount($locale = null);

    /**
     * Gets currency of money object
     * @return CurrencyInterface
     */
    public function getCurrency();

    /**
     * Adds money to the money object
     *
     * @param MoneyInterface $summand
     *
     * @return MoneyInterface
     */
    public function add(MoneyInterface $summand);

    /**
     * Subtracts money from money object
     *
     * @param MoneyInterface $subtrahend
     *
     * @return MoneyInterface
     */
    public function subtract(MoneyInterface $subtrahend);

    /**
     * Multiplies money object by given factor
     *
     * @param float   $factor
     * @param integer $mode
     *
     * @return MoneyInterface
     */
    public function multiply($factor, $mode = PHP_ROUND_HALF_UP);

    /**
     * Divides money object by given divisor
     *
     * @param float   $divisor
     * @param integer $mode
     *
     * @return MoneyInterface
     */
    public function divide($divisor, $mode = PHP_ROUND_HALF_UP);

    /**
     * Converts money object to object with different currency
     *
     * @param integer           $ratio
     * @param CurrencyInterface $currency
     * @param integer           $mode
     *
     * @return static
     */
    public function convertTo($ratio, $currency, $mode = PHP_ROUND_HALF_UP);

    /**
     * Compares money object to given money object
     * Returns < 0: Amount of object is smaller than given object
     * Returns 0: Amount is the same
     * Return > 0: Amount of object is bigger than given object
     *
     * @param MoneyInterface $money
     *
     * @return integer -|0|+
     */
    public function compareTo(MoneyInterface $money);

    /**
     * Returns new money object with same currency but given amount
     *
     * @param integer $amount
     *
     * @return MoneyInterface
     */
    public function newMoney($amount);

    /**
     * Asserts if the given money object has the same currency as the object itself
     *
     * @param MoneyInterface $money
     *
     * @throws CurrencyMismatchException
     */
    public function assertSameCurrency(MoneyInterface $money);

    /**
     * If the currency is the same as the currency of money
     *
     * @param MoneyInterface $money
     *
     * @return bool
     */
    public function isSameCurrency(MoneyInterface $money);
}
