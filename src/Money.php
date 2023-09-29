<?php
declare(strict_types=1);
namespace Nilz\Money;

use Locale;
use NumberFormatter;
use Nilz\Money\Currency\CurrencyInterface;
use Nilz\Money\Currency\ISO4217Currency;
use Nilz\Money\Exception\CurrencyMismatchException;

/**
 * Class Money
 *
 * @author Nilz
 */
class Money
{
    const ROUND_UP = 10;
    const ROUND_DOWN = 11;
    protected int $amount;
    protected CurrencyInterface $currency;

    /** @var Money[] */
    private ?array $currencies;

    public function __construct(int $amount, CurrencyInterface $currency, array $currencies = [])
    {
        $this->amount = $amount;
        $this->currency = $currency;
        $this->currencies = $currencies;
    }

    /**
     * Convenience method to get money object using float amount and currency alpha 3 string
     *
     * @param float   $amount   Converted amount, e.g. 4.20
     * @param string  $currency Alpha 3 string for currency, e.g. EUR
     * @param integer $mode     Rounding mode
     *
     * @return Money
     */
    public static function fromDefaultUnitAmount($amount, $currency, $mode = PHP_ROUND_HALF_UP)
    {
        $currency = new ISO4217Currency($currency);

        $amount = (int)round($amount * $currency->getFactorOfSmallestUnit(), 0, $mode);

        return new Money($amount, $currency);
    }

    /**
     * @inheritdoc
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultUnitAmount()
    {
        return round($this->amount / $this->currency->getFactorOfSmallestUnit(), $this->currency->getDecimalDigits());
    }

    /**
     * @inheritdoc
     */
    public function getFormattedAmount($locale = null, $hideFractionDigits = false)
    {
        $convertedAmount = $this->getDefaultUnitAmount();
        $formatter = $this->getCurrencyFormatter($locale);

        if ($hideFractionDigits) {
            //We dont display cents if there arent any. Php does not support that out of box, because we dont want numbers like 820,1€
            if ($this->getAmount() % 100 === 0) {
                $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 0);
            } else {
                $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 2);
            }
        }

        return $formatter->formatCurrency($convertedAmount, $this->getCurrency()->getAlpha3());
    }

    /**
     * @inheritdoc
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @inheritdoc
     */
    public function add(Money $summand)
    {
        $this->assertSameCurrency($summand);

        $sum = $this->amount + $summand->getAmount();

        return $this->newMoney($sum);
    }

    /**
     * @inheritdoc
     */
    public function subtract(Money $subtrahend)
    {
        $this->assertSameCurrency($subtrahend);

        $difference = $this->amount - $subtrahend->getAmount();

        return $this->newMoney($difference);
    }

    /**
     * @inheritdoc
     */
    public function multiply($factor, $mode = PHP_ROUND_HALF_UP): Money
    {
        return $this->convertTo($factor, $this->currency, $mode);
    }

    /**
     * @inheritdoc
     */
    public function divide($divisor, $mode = PHP_ROUND_HALF_UP): Money
    {
        $quotient = $this->round($this->amount / $divisor, $mode);

        return $this->newMoney($quotient);
    }

    /**
     * @inheritdoc
     */
    public function convertTo($ratio, CurrencyInterface $currency, $mode = PHP_ROUND_HALF_UP): Money
    {
        $currencyFactor = $currency->getFactorOfSmallestUnit() / $this->currency->getFactorOfSmallestUnit();

        $product = $this->round($this->amount * $ratio * $currencyFactor, $mode);

        return new static($product, $currency);
    }

    /**
     * @inheritdoc
     */
    public function compareTo(Money $money): int
    {
        $this->assertSameCurrency($money);

        return $this->amount - $money->getAmount();
    }

    /**
     * @inheritdoc
     */
    public function newMoney($amount): Money
    {
        return new static($amount, $this->currency);
    }

    /**
     * @param integer $amount
     * @param integer $mode
     *
     * @return float
     */
    protected function round($amount, $mode = PHP_ROUND_HALF_UP)
    {
        if ($mode === self::ROUND_UP) {
            return (int)ceil($amount);
        } else if ($mode === self::ROUND_DOWN) {
            return (int)floor($amount);
        }

        return (int)round($amount, 0, $mode);
    }

    /**
     * Returns number formatter class using the given locale
     *
     * @param string|null $locale e.g. en_CA, defaults to Locale::getDefault()
     *
     * @return NumberFormatter
     */
    protected function getCurrencyFormatter($locale = null)
    {
        $locale = $locale ?: Locale::getDefault();

        return NumberFormatter::create($locale, NumberFormatter::CURRENCY);
    }

    /**
     * @inheritdoc
     */
    public function assertSameCurrency(Money $money)
    {
        if (!$this->isSameCurrency($money)) {
            throw new CurrencyMismatchException(sprintf('Currency %s does not match %s of other object.', $this->getCurrency()->getAlpha3(), $money->getCurrency()->getAlpha3()));
        }
    }

    /**
     * @inheritdoc
     */
    public function isSameCurrency(Money $money)
    {
        if ($this->getCurrency()->getAlpha3() !== $money->getCurrency()->getAlpha3()) {
            return false;
        }
        return true;
    }

    public function getPriceInCurrency(string $currency, bool $useDefault = false): ?Money
    {
        // Iterate through the currency prices and find the matching currency
        foreach ($this->currencies as $price) {
            if ($price->getCurrency()->getAlpha3() === $currency) {
                return $price;
            }
        }

        // If the currency is not found and $useDefault is true, return the default price
        if ($useDefault) {
            return $this;
        }

        // If the currency is not found and $useDefault is false, return null
        return null;
    }

    public function getCurrencies(): ?array
    {
        return $this->currencies;
    }

    public function addCurrencyPrice(Money $price): void
    {
        $this->currencies[] = $price;
    }

    /**
     * @param Money[] $currencies
     */
    public function setCurrencies(array $currencies): void
    {
        $this->currencies = $currencies;
    }

    public function toArray(): array
    {
        return [
            'amount' => $this->getAmount(),
            'currency' => $this->getCurrency()->getAlpha3(),
            'currencies' => array_map(
                fn ($price) => [
                    'amount' => $price->getAmount(),
                    'currency' => $price->getCurrency()->getAlpha3(),
                ], $this->getCurrencies() ?? []
            )
        ];
    }

    public static function fromArray(array $value): Money
    {
        return new Money(
            $value['amount'],
            new ISO4217Currency($value['currency']),
            array_map(fn ($price) => new Money(
                (int) $price['amount'],
                new ISO4217Currency($price['currency'])),
                $value['currencies'] ?? []
            )
        );
    }
}
