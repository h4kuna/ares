<?php

namespace h4kuna\Ares;

use Nette\Object;

/**
 * Description of Get
 *
 * @author milan
 */
class Get extends Object {

    const URL = 'http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_bas.cgi?ico=';

    /** @var Data */
    protected $data;
    protected $IN;

    public function loadData($inn = NULL) {
        if ($this->data || $inn === NULL) {
            return $this->data;
        }

        return $this->loadXML($inn);
    }

    private function loadXML($inn) {
        $this->setIN($inn);
        $curl = new \h4kuna\CUrl(self::URL . $this->IN);
        $xmlSource = $curl->exec();
        if ($xmlSource) {
            $xml = @simplexml_load_string($xmlSource);
            if (!$xml) {
                throw new \h4kuna\AresException('No response.', 404);
            }
        } else {
            $curl->getErrors();
        }

        $ns = $xml->getDocNamespaces();
        $el = $xml->children($ns['are'])->children($ns['D'])->VBAS;

        $data = new Data();

        if (!isset($el->ICO)) {
            return $data;
        }

        $street = strval($el->AD->UC);
        if (is_numeric($street)) {
            $street = $el->AA->NCO . ' ' . $street;
        }

        $data->setIN($el->ICO)
                ->setTIN($el->DIC)
                ->setCity($el->AA->N)
                ->setCompany($el->OF)
                ->setStreet($street)
                ->setZip($el->AA->PSC);

        if (isset($el->ROR)) {
            $data->setActive($el->ROR->SOR->SSU)
                    ->setFileNumber($el->ROR->SZ->OV)
                    ->setCourt($el->ROR->SZ->SD->T);
        }

        return $data;
    }

    private function setIN($inn) {
        $this->IN = new \h4kuna\Int($inn);
        if (!preg_match('~^\d{6,9}$~', $this->IN->getValue())) {
            throw new \h4kuna\AresException('IN must be a number');
        }
        return TRUE;
    }

    public function clean() {
        $this->data = NULL;
    }

}

