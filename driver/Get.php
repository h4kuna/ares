<?php

namespace h4kuna\Ares;

use Nette\Object;

require_once 'IRequest.php';
require_once 'Data.php';

/**
 * Description of Get
 *
 * @author milan
 */
class Get extends Object implements IRequest {

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

        $this->data = new Data();
        return $this->data->setIN($el->ICO)
                        ->setTIN($el->DIC)
                        ->setCity($el->AA->N)
                        ->setCompany($el->OF)
                        ->setStreet($el->AD->UC)
                        ->setZip($el->AA->PSC);
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

