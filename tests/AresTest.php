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
        $in = '87744473';
        $ares = new Ares;
        $dir = __DIR__ . '/temp';
        $file = $dir . '/' . $in;
        if (!file_exists($file)) {
            $response = (string) $ares->loadData($in);
            if (@is_writable($dir)) {
                file_put_contents($file, $response);
            }
        } else {
            $response = file_get_contents($file);
        }

        $this->assertJsonStringEqualsJsonFile(__DIR__ . '/' . $in . '.json', $response);
    }

}
