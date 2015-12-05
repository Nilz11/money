<?php

namespace Nilz\Money\Currency;

use Alcohol\ISO4217;

/**
 * Class Currency
 *
 * Example implementation of currency object using iso 4217 provider. Uses alcohol/iso4217 to validate currencies. Feel free to use your
 * own.
 *
 * @author Nilz
 */
class ISO4217Currency implements CurrencyInterface
{
    /**
     * @var string
     */
    protected $alpha3;

    /**
     * @var integer
     */
    protected $exponent;

    /**
     * @param string $alpha3
     */
    public function __construct($alpha3)
    {
        $iso4217 = new ISO4217();

        $currency = $iso4217->getByAlpha3($alpha3);

        $this->alpha3 = $alpha3;
        $this->exponent = $currency['exp'];
    }

    /**
     * @inheritdoc
     */
    public function getFactorOfSmallestUnit()
    {
        return pow(10, $this->exponent);
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
        return $this->exponent;
    }
}
