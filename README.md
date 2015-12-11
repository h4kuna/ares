Ares
====

[![Build Status](https://travis-ci.org/h4kuna/ares.png?branch=master)](https://travis-ci.org/h4kuna/ares)

Is required guzzle/guzzle 6.1+ and php 5.5+. If you have php < 5.5 use older version [v1.0.6] it work but does not use guzzle.

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