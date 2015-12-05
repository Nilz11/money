<?php

namespace Nilz\Money\Currency;

/**
 * Class Currency
 *
 * @author Nilz
 */
class Currency implements CurrencyInterface
{
    /**
     * @var string
     */
    protected $alpha3;

    /**
     * @var integer
     */
    protected $factorOfSmallestUnit;

    /**
     * @var integer
     */
    protected $decimalDigits;

    /**
     * @param string  $alpha3
     * @param integer $factorOfSmallestUnit
     * @param integer $decimalDigits
     */
    public function __construct($alpha3, $factorOfSmallestUnit, $decimalDigits)
    {
        $this->alpha3 = $alpha3;
        $this->factorOfSmallestUnit = $factorOfSmallestUnit;
        $this->decimalDigits = $decimalDigits;
    }

    /**
     * @inheritdoc
     */
    public function getFactorOfSmallestUnit()
    {
        return $this->factorOfSmallestUnit;
    }

    /**
     * @inheritdoc
     */
    public function getAlpha3()
    {
        return $this->alpha3;
    }

    /**
     * @inheritdoc
     */
    public function getDecimalDigits()
    {
        return $this->decimalDigits;
    }
}
