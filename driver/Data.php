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

    public function toArray($map = array()) {
        if (!$map) {
            return $this->data;
        }
        $this->setFileNumberAndCourt();
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

