Ares
====

[![Build Status](https://travis-ci.org/h4kuna/ares.png?branch=master)](https://travis-ci.org/h4kuna/ares)


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
$ares = new \h4kuna\Ares\Ares();
$ares->loadData('87744473'); // return object \h4kuna\Ares\Data
```