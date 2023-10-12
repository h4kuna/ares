[![Downloads this Month](https://img.shields.io/packagist/dm/h4kuna/ares.svg)](https://packagist.org/packages/h4kuna/ares)
[![Latest Stable Version](https://poser.pugx.org/h4kuna/ares/v/stable?format=flat)](https://packagist.org/packages/h4kuna/ares)
[![Coverage Status](https://coveralls.io/repos/github/h4kuna/ares/badge.svg?branch=master)](https://coveralls.io/github/h4kuna/ares?branch=master)
[![Total Downloads](https://poser.pugx.org/h4kuna/ares/downloads?format=flat)](https://packagist.org/packages/h4kuna/ares)
[![License](https://poser.pugx.org/h4kuna/ares/license?format=flat)](https://packagist.org/packages/h4kuna/ares)

More information about versions is in [changelog](changelog.md).

## Support development by QR code

Use QR code or sponsor button where is link on my revolut.

Thank you :)

![QR payment](./.doc/payment.png)


## Installation to project

The best way to install h4kuna/ares is using Composer:

```sh
composer require h4kuna/ares
```

Download information about customer via his IN.

## ARES

[Homepage](https://ares.gov.cz/stranky/vyvojar-info) documentation for developers.

Load data by one identification number.

```php
use h4kuna\Ares;
$ares = (new Ares\AresFactory())->create();
try {
    $response = $ares->loadBasic('87744473');
    /* @var $response Ares\Ares\Core\Data */
    var_dump($response);
} catch (Ares\Exceptions\IdentificationNumberNotFoundException $e) {
    // log identification number, why is bad? Or make nothing.
} catch (Ares\Exceptions\ServerResponseException $e) {
    // no response from server or broken json
}
```

Load data by many identification numbers. Limit by ARES service is set to 100 items, but library chunk it and check duplicity.

```php
use h4kuna\Ares;
/** @var Ares\Ares $ares */
$numbers = ['one' => '25596641', 'two' => '26713250', 'three' => '27082440', 'four' => '11111111'];

try { 
    foreach ($ares->loadBasicMulti($numbers) as $name => $r) {
        var_dump($name, $r->company);
    }
} catch (Ares\Exceptions\ServerResponseException $e) {
    // no response from server or broken json
}
```

## Data Box (datavá schánka)

[Manual](https://www.mojedatovaschranka.cz/sds/p/download/sds_webove_sluzby.pdf#view=Fit)

```php
use h4kuna\Ares;
/** @var Ares\Ares $ares */
try {
    $response = $ares->loadDataBox('87744473');
    var_dump($response->ISDS);
} catch (h4kuna\Ares\Exceptions\ServerResponseException $e) {
    // catch error
}
```
