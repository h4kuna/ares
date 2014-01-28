<?php

namespace Tests;

require_once __DIR__ . '/../vendor/autoload.php';

use h4kuna\Ares;
use PHPUnit_Framework_TestCase;

/**
 * @author Milan Matějček
 */
class AresTest extends PHPUnit_Framework_TestCase {

    public function testResponse() {
        $ares = new Ares;
        $this->assertJsonStringEqualsJsonFile(__DIR__ . '/87744473.json', (string) $ares->loadData('87744473'));
    }

}
