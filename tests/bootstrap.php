<?php declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('Europe/Prague');

function loadResult(string $name, \stdClass $save = null): \stdClass
{
	$file = __DIR__ . "/fixtures/response/$name.json";
	if ($save !== null) {
		file_put_contents($file, json_encode($save, JSON_PRETTY_PRINT));
	}

	return json_decode(file_get_contents($file));
}


Tracy\Debugger::enable(false);

if (!defined('__PHPSTAN_RUNNING__')) {
	Tester\Environment::setup();
}
