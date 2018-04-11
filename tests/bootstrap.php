<?php

include __DIR__ . "/../vendor/autoload.php";

\Salamium\Testinium\File::setRoot(__DIR__ . '/data');

Tracy\Debugger::enable(false);

Tester\Environment::setup();
