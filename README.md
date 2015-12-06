[<img src="https://travis-ci.org/Nilz11/money.svg?branch=master">](https://travis-ci.org/Nilz11/money)

A real world implementation of an extendable Money object using integers of smallest currency unit as calculation.

This library was created because other existing solutions had one or more of the following drawbacks:
- missing extendability of Money class
- dependency on fixed currency class
- missing methods like format and convertTo currency
- errors in implementation that caused to have rounding errors and missing cents in e.g. summing up prices
- library not being maintained anymore

Free to use. Use it or leave it.

#### Examples

Create a money object:

```php
use Nilz\Money\Money;
use Nilz\Money\Currency\ISO4217Currency;

$money = new Money(420, new ISO4217Currency('EUR'));

$money = Money::fromDefaultUnitAmount('4.20', 'EUR');
```
---

Get amount of money object:

```php

//420
echo $money->getAmount();

//4.20
echo $money->getDefaultUnitAmount();

//4,20 €
echo $money->getFormattedAmount('de_DE');

```

---

Sum up two money objects:

```php

$a = Money::fromDefaultUnitAmount('4.20', 'EUR');
$b = Money::fromDefaultUnitAmount('2.10', 'EUR');

$c = $a->add($b);

//6.30
echo $c->getAmount();

//4.20
echo $a->getAmount();

//2.10
echo $b->getAmount();

```

Other arithmetic examples:

```php

$a = Money::fromDefaultUnitAmount('4.20', 'EUR');
$b = Money::fromDefaultUnitAmount('2.10', 'EUR');

$a->subtract($b);

$a->multiply(1.2);
$a->divide(1.2);

```

If you need other methods to perform calculations, you can easily extend the money object or put a pull request to extend it.

---

Use custom currency:

```php
use Nilz\Money\Currency\Currency;

//alpha3 code, factor for smallest unit representation, decimal digits to round two
$money = new Money(420, new Currency('EUR', 100, 2));

```

You can also implement a custom currency object by implementing the CurrencyInterface.
