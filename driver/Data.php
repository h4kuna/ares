<?php

namespace h4kuna\Ares;

use Nette\Object;

/**
 * Description of Data
 *
 * @author milan
 */
class Data extends Object {

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

    private function set($key, $val) {
        $this->data[$key] = strval($val);
        return $this;
    }

    /**
     * You can define your own keys
     * @example
     * $map = array('tin' => 'dic', 'company' => 'spolecnost')
     * @param array $map
     * @return array
     */
    public function toArray($map = array()) {
        if (!$map) {
            return $this->data;
        }

        $out = array();
        foreach ($map as $k => $v) {
            if (array_key_exists($k, $this->data)) {
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

}

