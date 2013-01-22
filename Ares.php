<?php

namespace h4kuna;

use Nette\Object;

/**
 * @author Milan Matějček <milan.matejcek@gmail.com>
 *
 * @example
  $ares = new Ares;
  var_dump($ares->send('87744473'));
 */
class Ares extends Object {

    /** @var Ares\Get */
    private $class;

    public function __construct($class = 'Get') {
        $file = __DIR__ . '/driver/' . $class . '.php';
        if (!file_exists($file)) {
            throw new AresException('Service is not supported ' . $class . ' in file ' . $file);
        }
        require_once $file;

        $class = __NAMESPACE__ . '\Ares\\' . $class;
        $this->class = new $class;
    }

    public function loadData($inn) {
        $this->class->clean();
        return $this->class->loadData($inn);
    }

    public function getData() {
        return $this->class->loadData();
    }

}

class AresException extends \RuntimeException {

}
