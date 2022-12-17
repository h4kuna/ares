<?php declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

function loadResult(string $name, string $save = ''): \stdClass
{
	$file = __DIR__ . "/fixtures/response/$name.json";
	if ($save !== '') {
		file_put_contents($file, $save);
	}

	return json_decode(file_get_contents($file));
}


Tracy\Debugger::enable(false);

if (!defined('__PHPSTAN_RUNNING__')) {
	Tester\Environment::setup();
}
