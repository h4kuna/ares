Ares
====

[![Build Status](https://travis-ci.org/h4kuna/ares.png?branch=master)](https://travis-ci.org/h4kuna/ares)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/h4kuna/ares/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/h4kuna/ares/?branch=master)
[![Downloads this Month](https://img.shields.io/packagist/dm/h4kuna/ares.svg)](https://packagist.org/packages/h4kuna/ares)
[![Latest Stable Version](https://poser.pugx.org/h4kuna/ares/v/stable?format=flat)](https://packagist.org/packages/h4kuna/ares)
[![Coverage Status](https://coveralls.io/repos/github/h4kuna/ares/badge.svg?branch=master)](https://coveralls.io/github/h4kuna/ares?branch=master)
[![Total Downloads](https://poser.pugx.org/h4kuna/ares/downloads?format=flat)](https://packagist.org/packages/h4kuna/ares)
[![License](https://poser.pugx.org/h4kuna/ares/license?format=flat)](https://packagist.org/packages/h4kuna/ares)

More information about versions is in [changelog](changelog.md).

Installation to project
-----------------------
The best way to install h4kuna/ares is using Composer:
```sh
$ composer require h4kuna/ares
```

Download information about customer via his IN.

Example
-------
```php
$ares = new h4kuna\Ares\Ares();
try {
    $response = $ares->loadData('87744473');
    /* @var $response h4kuna\Ares\Data */
    var_dump($response);
} catch (h4kuna\Ares\Exceptions\IdentificationNumberNotFoundException $e) {
    // log identification number, why is bad? Or make nothing.
}
```
