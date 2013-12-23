<?php

namespace h4kuna;

use Nette\Object;

/**
 * @author Milan Matějček <milan.matejcek@gmail.com>
 *
 * @example
  $ares = new Ares;
  var_dump($ares->loadData('87744473'));
 */
class Ares extends Object {

    /** @var Ares\IRequest */
    private $class;

    public function __construct(Ares\IRequest $obj = NULL) {
        if ($obj === NULL) {
            $obj = new Ares\Get();
        }

        $this->class = $obj;
    }

    /**
     * Load fresh data
     *
     * @param int|string $inn
     * @return Ares\Data
     */
    public function loadData($inn) {
        $this->class->clean();
        return $this->class->loadData($inn);
    }

    /**
     * Get temporary data
     *
     * @return Ares\Data
     */
    public function getData() {
        return $this->class->loadData();
    }

}

class AresException extends \RuntimeException {

}
