<?php

namespace h4kuna\Ares;

use h4kuna\AresException;
use h4kuna\CUrl\CurlBuilder;
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

    public function __construct(Data $data = NULL) {
        if ($data === NULL) {
            $data = new Data;
        }
        $this->data = $data;
    }

    public function loadData($inn = NULL) {
        if ($inn === NULL) {
            return $this->data;
        }

        return $this->loadXML($inn);
    }

    /**
     * Load XML and fill Data object
     *
     * @param string $inn
     * @return Data
     * @throws AresException
     */
    private function loadXML($inn) {
        $this->clean();
        $IN = new Int($inn);
        $xmlSource = CurlBuilder::download(self::URL . (string) $IN);
        $xml = @simplexml_load_string($xmlSource);
        if (!$xml) {
            throw new AresException('No response.', 404);
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
