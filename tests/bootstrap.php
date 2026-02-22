<?php declare(strict_types = 1);

use Tester\Environment;
use Tracy\Debugger;

require_once __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('Europe/Prague');

Debugger::enable(false);

if (defined('__PHPSTAN_RUNNING__') === false) {
	Environment::setup();
}
