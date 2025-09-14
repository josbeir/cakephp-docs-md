---
title: NumberHelper
description: The Number Helper contains convenience methods that enable display numbers in common formats in your views.
keywords: "number helper,currency,number format,number precision,format file size,format numbers"
---

# Number

**Namespace:** `Cake\I18n`

### Class `Cake\I18n\Number`

If you need `NumberHelper` functionalities outside of a `View`,
use the `Number` class

```php
namespace App\Controller;

use Cake\I18n\Number;

class UsersController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Authentication.Authentication');
    }

    public function afterLogin()
    {
        $identity = $this->Authentication->getIdentity();
        $storageUsed = $identity->storage_used;
        if ($storageUsed > 5000000) {
            // Notify users of quota
            $this->Flash->success(__('You are using {0} storage', Number::toReadableSize($storageUsed)));
        }
    }
}

```

.. start-cakenumber

All of these functions return the formatted number; they do not
automatically echo the output into the view.

## Formatting Currency Values

#### Method `Cake\I18n\Number::currency(mixed $value, string $currency = null, array $options = [])`

This method is used to display a number in common currency formats
(EUR, GBP, USD), based on the 3-letter ISO 4217 currency code. Usage in a view looks like

```php
// Called as NumberHelper
echo $this->Number->currency($value, $currency);

// Called as Number
echo Number::currency($value, $currency);

```

The first parameter, `$value`, should be a floating point number
that represents the amount of money you are expressing. The second
parameter is a string used to choose a predefined currency formatting
scheme:

| $currency | 1234.56, formatted by currency type |
| --- | --- |
| EUR | €1.234,56 |
| GBP | £1,234.56 |
| USD | $1,234.56 |

The third parameter is an array of options for further defining the
output. The following options are available:

| Option | Description |
| --- | --- |
| before | Text to display before the rendered number. |
| after | Text to display after the rendered number. |
| zero | The text to use for zero values; can be a string or a number. ie. 0, 'Free!'. |
| places | Number of decimal places to use, ie. 2 |
| precision | Maximal number of decimal places to use, ie. 2 |
| locale | The locale name to use for formatting number, ie. "fr_FR". |
| fractionSymbol | String to use for fraction numbers, ie. ' cents'. |
| fractionPosition | Either 'before' or 'after' to place the fraction symbol. |
| pattern | An ICU number pattern to use for formatting the number ie. #,###.00 |
| useIntlCode | Set to `true` to replace the currency symbol with the international currency code. |

If `$currency` value is `null`, the default currency will be retrieved from
`Cake\I18n\Number::defaultCurrency()`. To format currencies in an
accounting format you should set the currency format

```php
Number::setDefaultCurrencyFormat(Number::FORMAT_CURRENCY_ACCOUNTING);

```

## Setting the Default Currency

#### Method `Cake\I18n\Number::setDefaultCurrency($currency)`

Setter for the default currency. This removes the need to always pass the
currency to `Cake\I18n\Number::currency()` and change all
currency outputs by setting other default. If `$currency` is set to `null`,
it will clear the currently stored value.

## Getting the Default Currency

#### Method `Cake\I18n\Number::getDefaultCurrency()`

Getter for the default currency. If default currency was set earlier using
`setDefaultCurrency()`, then that value will be returned. By default, it will
retrieve the `intl.default_locale` ini value if set and `'en_US'` if not.

## Formatting Floating Point Numbers

#### Method `Cake\I18n\Number::precision(float $value, int $precision = 3, array $options = [])`

This method displays a number with the specified amount of
precision (decimal places). It will round in order to maintain the
level of precision defined.

```php
// Called as NumberHelper
echo $this->Number->precision(456.91873645, 2);

// Outputs
456.92

// Called as Number
echo Number::precision(456.91873645, 2);

```

## Formatting Percentages

#### Method `Cake\I18n\Number::toPercentage(mixed $value, int $precision = 2, array $options = [])`

| Option | Description |
| --- | --- |
| multiply | Boolean to indicate whether the value has to be multiplied by 100. Useful for decimal percentages. |

Like `Cake\I18n\Number::precision()`, this method formats a number
according to the supplied precision (where numbers are rounded to meet the
given precision). This method also expresses the number as a percentage
and appends the output with a percent sign.

```php
// Called as NumberHelper. Output: 45.69%
echo $this->Number->toPercentage(45.691873645);

// Called as Number. Output: 45.69%
echo Number::toPercentage(45.691873645);

// Called with multiply. Output: 45.7%
echo Number::toPercentage(0.45691, 1, [
    'multiply' => true
]);

```

## Interacting with Human Readable Values

#### Method `Cake\I18n\Number::toReadableSize(string $size)`

This method formats data sizes in human readable forms. It provides
a shortcut way to convert bytes to KB, MB, GB, and TB. The size is
displayed with a two-digit precision level, according to the size
of data supplied (i.e. higher sizes are expressed in larger
terms)

```php
// Called as NumberHelper
echo $this->Number->toReadableSize(0); // 0 Byte
echo $this->Number->toReadableSize(1024); // 1 KB
echo $this->Number->toReadableSize(1321205.76); // 1.26 MB
echo $this->Number->toReadableSize(5368709120); // 5 GB

// Called as Number
echo Number::toReadableSize(0); // 0 Byte
echo Number::toReadableSize(1024); // 1 KB
echo Number::toReadableSize(1321205.76); // 1.26 MB
echo Number::toReadableSize(5368709120); // 5 GB

```

## Formatting Numbers

#### Method `Cake\I18n\Number::format(mixed $value, array $options = [])`

This method gives you much more control over the formatting of
numbers for use in your views (and is used as the main method by
most of the other NumberHelper methods). Using this method might
looks like

```php
// Called as NumberHelper
$this->Number->format($value, $options);

// Called as Number
Number::format($value, $options);

```

The `$value` parameter is the number that you are planning on
formatting for output. With no `$options` supplied, the number
1236.334 would output as 1,236. Note that the default precision is
zero decimal places.

The `$options` parameter is where the real magic for this method
resides.

-  If you pass an integer then this becomes the amount of precision
or places for the function.
-  If you pass an associated array, you can use the following keys:

| Option | Description |
| --- | --- |
| places | Number of decimal places to use, ie. 2 |
| precision | Maximum number of decimal places to use, ie. 2 |
| pattern | An ICU number pattern to use for formatting the number ie. #,###.00 |
| locale | The locale name to use for formatting number, ie. "fr_FR". |
| before | Text to display before the rendered number. |
| after | Text to display after the rendered number. |

Example

```php
// Called as NumberHelper
echo $this->Number->format('123456.7890', [
    'places' => 2,
    'before' => '¥ ',
    'after' => ' !'
]);
// Output '¥ 123,456.79 !'

echo $this->Number->format('123456.7890', [
    'locale' => 'fr_FR'
]);
// Output '123 456,79 !'

// Called as Number
echo Number::format('123456.7890', [
    'places' => 2,
    'before' => '¥ ',
    'after' => ' !'
]);
// Output '¥ 123,456.79 !'

echo Number::format('123456.7890', [
    'locale' => 'fr_FR'
]);
// Output '123 456,79 !'

```

#### Method `Cake\I18n\Number::ordinal(mixed $value, array $options = [])`

This method will output an ordinal number.

Examples

```php
echo Number::ordinal(1);
// Output '1st'

echo Number::ordinal(2);
// Output '2nd'

echo Number::ordinal(2, [
    'locale' => 'fr_FR'
]);
// Output '2e'

echo Number::ordinal(410);
// Output '410th'

```

## Format Differences

#### Method `Cake\I18n\Number::formatDelta(mixed $value, array $options = [])`

This method displays differences in value as a signed number

```php
// Called as NumberHelper
$this->Number->formatDelta($value, $options);

// Called as Number
Number::formatDelta($value, $options);

```

The `$value` parameter is the number that you are planning on
formatting for output. With no `$options` supplied, the number
1236.334 would output as 1,236. Note that the default precision is
zero decimal places.

The `$options` parameter takes the same keys as `Number::format()` itself:

| Option | Description |
| --- | --- |
| places | Number of decimal places to use, ie. 2 |
| precision | Maximum number of decimal places to use, ie. 2 |
| locale | The locale name to use for formatting number, ie. "fr_FR". |
| before | Text to display before the rendered number. |
| after | Text to display after the rendered number. |

Example

```php
// Called as NumberHelper
echo $this->Number->formatDelta('123456.7890', [
    'places' => 2,
    'before' => '[',
    'after' => ']'
]);
// Output '[+123,456.79]'

// Called as Number
echo Number::formatDelta('123456.7890', [
    'places' => 2,
    'before' => '[',
    'after' => ']'
]);
// Output '[+123,456.79]'

```

.. end-cakenumber

## Configure formatters

#### Method `Cake\I18n\Number::config(string $locale, int $type = NumberFormatter::DECIMAL, array $options = [])`

This method allows you to configure formatter defaults which persist across calls
to various methods.

Example

```php
Number::config('en_IN', \NumberFormatter::CURRENCY, [
    'pattern' => '#,##,##0'
]);

```