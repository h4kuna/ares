<?php declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('Europe/Prague');

Tracy\Debugger::enable(false);

if (!defined('__PHPSTAN_RUNNING__')) {
	Tester\Environment::setup();
}
