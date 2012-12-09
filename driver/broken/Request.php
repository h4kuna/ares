<?php

namespace h4kuna\Ares;

use Nette\Object;

abstract class Request extends Object {

    const XML = '<?xml version="1.0" encoding="UTF-8"?>';

    private $count = 0;
    private static $response = array(
        'Client.RequestNamespace' => 'Jmenný prostor dotazu není podporovaný',
        'Server.Service' => 'Obecná chyba serverové služby',
        1 => 'Nelze se připojit k databázi nebo na zadaný Dotaz by se vrátilo více odpovědí, než odpovídá parametru',
        2 => 'Dokument neodpovídá schématu (non valid dokument)',
        3 => 'Syntaktická chyba dokumentu (non well-formed dokument)',
        4 => 'Chyba na straně poskytující aplikace (nepřístupná databáze, jiná chyba zpracování)',
        5 => 'Status věty neuveden',
        6 => 'Chyba SQL dotazu',
        7 => 'Chyba logických vazeb vstupních dat v dotazu',
        8 => 'Nekonzistence dat v databázi',
        9 => 'Nepodporovaná verze dotazu nebo vstupních parametrů',
        10 => 'Záznam je u Obchodního rejstříku přepisován. Nebude vystavováno.',
        11 => 'Požadovaná služba není veřejně přístupná.',
        99 => 'Ostatní chyby',
    );

    /**
     * @var \Nette\Utils\Html
     */
    protected $xml;
    protected $dotaz = array();

    /*
      //<Dotaz>
      //<Pomocne_ID>1</Pomocne_ID>
      //<ICO>27166201</ICO>
      //</Dotaz>
     */

    public function __construct() {
        $this->xml = NHtml::el('are:Ares_dotazy');
        $this->xml->__set('xmlns:are', 'http://wwwinfo.mfcr.cz/ares/xml_doc/schemas/ares/ares_request_orrg/v_1.0.0');
        $this->xml->__set('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $this->xml->__set('xsi:schemaLocation', 'http://wwwinfo.mfcr.cz/ares/xml_doc/schemas/ares/ares_request_orrg/v_1.0.0');
        $this->xml->__set('dotaz_datum_cas', date('Y-m-d\TH:i:s'));
        $this->xml->__set('dotaz_typ', 'Basic');
        $this->xml->__set('vystup_format', 'XML');
        $this->xml->__set('validation_XSLT', 'http://wwwinfo.mfcr.cz/ares/xml_doc/schemas/ares/ares_answer/v_1.0.0/ares_answer.xsl');
        $this->xml->__set('user_mail', 'admin@slevomat.cz');
        $this->xml->__set('answerNamespaceRequired', 'http://wwwinfo.mfcr.cz/ares/xml_doc/schemas/ares/ares_answer_basic/v_1.0.3');
        $this->xml->__set('Id', 'Ares_dotaz');
    }

    public function setIc($val) {
        ++$this->count;
        $this->dotaz[$this->count] = $val;
    }

    public function render() {
        if (empty($this->dotaz))
            throw new AresException('Vyplň alespoň jeden IČ.');
        foreach ($this->dotaz as $key => $val) {
            $this->xml->add('<Dotaz><Pomocne_ID>' . $key . '</Pomocne_ID><ICO>' . $val . '</ICO></Dotaz>');
        }
        $this->xml->__set('dotaz_pocet', $this->count);
        return $this->xml->render();
    }

}