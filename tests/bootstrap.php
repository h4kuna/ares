<?php

include __DIR__ . "/../vendor/autoload.php";

require_once __DIR__ . '/Utils.php';

function dd($var /* ... */)
{
	foreach (func_get_args() as $arg) {
		\Tracy\Debugger::dump($arg);
	}
	exit;
}

Tracy\Debugger::enable(FALSE);
Tester\Environment::setup();




