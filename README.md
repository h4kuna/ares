[![Downloads this Month](https://img.shields.io/packagist/dm/h4kuna/ares.svg)](https://packagist.org/packages/h4kuna/ares)
[![Latest Stable Version](https://poser.pugx.org/h4kuna/ares/v/stable?format=flat)](https://packagist.org/packages/h4kuna/ares)
[![Coverage Status](https://coveralls.io/repos/github/h4kuna/ares/badge.svg?branch=main)](https://coveralls.io/github/h4kuna/ares?branch=main)
[![Total Downloads](https://poser.pugx.org/h4kuna/ares/downloads?format=flat)](https://packagist.org/packages/h4kuna/ares)
[![License](https://poser.pugx.org/h4kuna/ares/license?format=flat)](https://packagist.org/packages/h4kuna/ares)

Part of the [h4kuna PHP libraries](https://github.com/h4kuna/library), see the overview of all packages.

More information about versions is in [changelog](changelog.md).

## Support development by QR code

Use the QR code or the sponsor button, which links to my Revolut.

Thank you :)

![QR payment](./.doc/payment.png)


## Installation to project

Requires PHP 8.0 or newer. The best way to install h4kuna/ares is using Composer:

```sh
composer require h4kuna/ares

# optional, default implementation of PSR-7, PSR-17 and PSR-18 used by AresFactory
composer require guzzlehttp/guzzle
```

Without Guzzle, pass your own PSR-18 client and PSR-17 factories to the `AresFactory` constructor.

The library downloads information about a subject by its identification number (IČO).

## ARES

[Documentation for developers](https://ares.gov.cz/stranky/vyvojar-info).

Load data by one identification number. Shorter numbers are padded with leading zeros to 8 digits.

```php
use h4kuna\Ares;
$ares = (new Ares\AresFactory())->create();
try {
    $response = $ares->loadBasic('87744473');
    /* @var $response Ares\Ares\Core\Data */
    var_dump($response);
} catch (Ares\Exception\IdentificationNumberNotFoundException $e) {
    // the identification number does not exist, log it or ignore it
} catch (Ares\Exception\AdisResponseException $e) {
    // validation by ADIS failed, but ARES returned the data
    /* @var $response Ares\Ares\Core\Data */
    $response = $e->data;
    $response->adis === null; // true
    var_dump($e->getMessage());
} catch (Ares\Exception\ServerResponseException $e) {
    // no response from server or broken json
}
```

Load data by many identification numbers. The ARES service accepts at most 100 items per request, the library splits the input into chunks and removes duplicates. The result is a `Generator` that keeps the keys of the input array and contains only the subjects that exist.

```php
use h4kuna\Ares;
/** @var Ares\Ares $ares */
$numbers = ['one' => '25596641', 'two' => '26713250', 'three' => '27082440', 'four' => '11111111'];

try {
    foreach ($ares->loadBasicMulti($numbers) as $name => $r) {
        var_dump($name, $r->company);
    }
} catch (Ares\Exception\ResultException | Ares\Exception\ServerResponseException $e) {
    // error response, no response from server or broken json
}
```

### Other endpoints

Choose an endpoint from the class [Sources](./src/Ares/Sources.php).
- `SERVICE_*` - other available endpoints
- `CORE` - the main endpoint, used by the method `$ares->loadBasic()`
- `DIAL` - code lists (dials), for example `PravniForma`
- `SER_NO_*` - not supported

Runnable examples are in [bin](./bin).

```php
use h4kuna\Ares;

/** @var Ares\Ares $ares */
$result = $ares->getAresClient()->useEndpoint(Ares\Ares\Sources::SERVICE_VR, '27082440');
var_dump($result);
```

#### Dials

You can find the parameters `kodCiselniku` and `zdrojCiselniku` in the JSON file [AresRestApi-verejne_v*.json](https://ares.gov.cz/stranky/vyvojar-info), for example `ciselnikKod: PravniForma, zdroj: res`.

```php
use h4kuna\Ares;

/** @var Ares\Ares $ares */
$result = $ares->getAresClient()->searchEndpoint(Ares\Ares\Sources::DIAL, [
	'kodCiselniku' => 'PravniForma',
	'zdrojCiselniku' => 'res',
])->ciselniky[0]->polozkyCiselniku;

foreach ($result as $item) {
	var_dump($item);
}
```

## Data Box (datová schránka)

[Manual](https://www.mojedatovaschranka.cz/sds/p/download/sds_webove_sluzby.pdf#view=Fit)

```php
use h4kuna\Ares;
/** @var Ares\Ares $ares */
try {
    // returns a list of data boxes of the subject
    foreach ($ares->loadDataBox('87744473') as $dataBox) {
        var_dump($dataBox->ISDS);
    }
} catch (Ares\Exception\ResultException | Ares\Exception\ServerResponseException $e) {
    // catch error
}
```

## VIES

Supports the [basic VAT number check](https://ec.europa.eu/taxation_customs/vies/). The VAT number must start with the country code, or pass an instance of `Ares\Vies\ViesEntity`.

```php
use h4kuna\Ares;
/** @var Ares\Ares $ares */

try {
    $response = $ares->checkVatVies('CZ27082440');
    var_dump($response->valid); // true / false
} catch (Ares\Exception\ServerResponseException $e) {
    // service error, for example MS_UNAVAILABLE
}
```
