<?php

namespace h4kuna\Ares;

use h4kuna\AresException;
use h4kuna\CUrl;
use h4kuna\Int;
use Nette\Object;

/**
 * Description of Get
 *
 * @author Milan Matějček
 */
class Get extends Object implements IRequest {

    const URL = 'http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_bas.cgi?ico=';

    /** @var Data */
    protected $data;
    protected $IN;

    public function __construct(Data $data = NULL) {
        $this->data = $data ? $data : new Data;
    }

    public function loadData($inn = NULL) {
        if ($inn === NULL) {
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
        $xmlEl = $xml->children($ns['are'])->children($ns['D'])->VBAS;

        if (!isset($xmlEl->ICO)) {
            return $this->data;
        }

        $street = strval($xmlEl->AD->UC);
        if (is_numeric($street)) {
            $street = $xmlEl->AA->NCO . ' ' . $street;
        }

        if (isset($xmlEl->AA->CO)) {
            $street .= '/' . $xmlEl->AA->CO;
        }

        $this->data->setIN($xmlEl->ICO)
                ->setTIN($xmlEl->DIC)
                ->setCity($xmlEl->AA->N)
                ->setCompany($xmlEl->OF)
                ->setStreet($street)
                ->setZip($xmlEl->AA->PSC)
                ->setPerson($xmlEl->PF->KPF)
                ->setCreated($xmlEl->DV);

        if (isset($xmlEl->ROR)) {
            $this->data->setActive($xmlEl->ROR->SOR->SSU)
                    ->setFileNumber($xmlEl->ROR->SZ->OV)
                    ->setCourt($xmlEl->ROR->SZ->SD->T);
        }

        return $this->data;
    }

    /**
     * Clear data
     *
     * @return Get
     */
    public function clean() {
        $this->data->clean();
        return $this;
    }

}
