<?php

namespace h4kuna\Ares;

use Nette\Object;

/**
 * Description of Data
 *
 * @author milan
 */
class Data extends Object implements \ArrayAccess {

    private $data = array();

    public function setIN($s) {
        return $this->set('in', $s);
    }

    public function setTIN($s) {
        $this->set('tin', $s);
        return $this->set('vat_pay', (bool) $this->data['tin']);
    }

    public function setCompany($s) {
        return $this->set('company', $s);
    }

    public function setStreet($s) {
        return $this->set('street', $s);
    }

    public function setCity($s) {
        return $this->set('city', $s);
    }

    public function setZip($s) {
        return $this->set('zip', $s);
    }

    public function setActive($s) {
        $this->data['active'] = strval($s) == 'Aktivní';
        return $this;
    }

    public function setFileNumber($s) {
        return $this->set('file_number', $s);
    }

    public function setCourt($s) {
        return $this->set('court', $s);
    }

    private function setFileNumberAndCourt() {
        if (array_key_exists('file_number', $this->data) && array_key_exists('court', $this->data)) {
            $this->data['court_all'] = $this->data['file_number'] . ', ' . $this->data['court'];
        }
    }

    private function set($key, $val) {
        $this->data[$key] = strval($val);
        return $this;
    }

    public function clean() {
        $this->data = array();
    }

    /**
     * copy data
     * @param array $map
     * @return array
     */
    public function toArray(array $map = array()) {
        $this->setFileNumberAndCourt();
        if (!$map) {
            return $this->data;
        }
        $out = array();
        foreach ($map as $k => $v) {
            if ($this->offsetExists($k)) {
                if (!$v) {
                    $v = $k;
                }
                $out[$v] = $this->data[$k];
            }
        }
        return $out;
    }

    public function __toString() {
        return json_encode($this->data);
    }

// ---------------- ArrayAccess
    public function offsetExists($offset) {
        return array_key_exists($offset, $this->data);
    }

    public function offsetGet($offset) {
        if ($this->offsetExists($offset)) {
            return $this->data[$offset];
        }
        throw new \h4kuna\AresException('Undefined offset: ' . $offset);
    }

    public function offsetSet($offset, $value) {
        return $this->data[$offset] = $value;
    }

    public function offsetUnset($offset) {
        unset($this->data[$offset]);
    }

}

