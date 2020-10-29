<?php declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/TestCase.php';

Salamium\Testinium\File::setRoot(__DIR__ . '/data');

Tracy\Debugger::enable(false);

if (!\defined('__PHPSTAN_RUNNING__')) {
	Tester\Environment::setup();
}
