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

# optional and default support
composer require guzzlehttp/guzzle
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

### Other endpoints

Choose endpoint from class [Sources](./src/Ares/Sources.php).
- SERVICE_* - available other endpoints
- CORE - is main endpoint this is used in method `$ares->loadBasic()`
- DIAL - use if you want list of value for example `PravniForma`
- SER_NO_* are not exists

```php
use h4kuna\Ares;

/** @var Ares\Ares $ares */
$result = $ares->getAresClient()->useEndpoint(Ares\Ares\Sources::SERVICE_VR, '27082440');
var_dump($result);
```

#### Dials

Parameters `kodCiselniku` and `zdrojCiselniku` you can find in json file, on this page [AresRestApi-verejne_v*.json](https://ares.gov.cz/stranky/vyvojar-info), like a `ciselnikKod: PravniForma, zdroj: res`.

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

## Data Box (datová schánka)

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

## VIES

Support [base check](https://ec.europa.eu/taxation_customs/vies/).

```php
use h4kuna\Ares;
/** @var Ares\Ares $ares */

try {
    $response = $ares->checkVatVies($vatNumber);
    var_dump($response->valid); // true / false
} catch (Ares\Exceptions\ServerResponseException $e) {
    // invalid VAT
}
```
