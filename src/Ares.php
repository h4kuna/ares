<?php

namespace h4kuna\Ares;

use GuzzleHttp;

/**
 * @author Milan Matějček <milan.matejcek@gmail.com>
 */
class Ares
{

	const URL = 'http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_bas.cgi?ico=';

	/** @var DataProvider */
	private $dataProvider;

	public function __construct(DataProvider $dataProvider = NULL)
	{
		if ($dataProvider === NULL) {
			$dataProvider = $this->createDataProvider();
		}
		$this->dataProvider = $dataProvider;
	}

	/**
	 * Load fresh data.
	 * @param int|string $inn
	 * @throws InNotFoundExceptions
	 * @return IData
	 */
	public function loadData($inn)
	{
		$this->loadXML($inn);
		return $this->getData();
	}

	/**
	 * Get temporary data.
	 * @return IData
	 */
	public function getData()
	{
		return $this->dataProvider->getData();
	}

	/**
	 * Load XML and fill Data object
	 * @param string $inn
	 * @throws InNotFoundExceptions
	 */
	private function loadXML($inn)
	{
		$client = new GuzzleHttp\Client();
		$xmlSource = $client->request('GET', self::URL . (string) $inn)->getBody();
		$xml = @simplexml_load_string($xmlSource);
		if (!$xml) {
			throw new InNotFoundExceptions;
		}

		$ns = $xml->getDocNamespaces();
		$xmlEl = $xml->children($ns['are'])->children($ns['D'])->VBAS;

		if (!isset($xmlEl->ICO)) {
			throw new InNotFoundExceptions;
		}

		$this->processXml($xmlEl, $this->dataProvider->prepareData());
	}

	protected function processXml($xml, DataProvider $dataProvider)
	{
		$dataProvider->setIN($xml->ICO)
			->setTIN($xml->DIC)
			->setCity($xml->AA->N)
			->setCompany($xml->OF)
			->setStreet($xml->AD->UC, $xml->AA->NCO, isset($xml->AA->CO) ? $xml->AA->CO : NULL)
			->setZip($xml->AA->PSC)
			->setPerson($xml->PF->KPF)
			->setCreated($xml->DV);

		if (isset($xml->ROR)) {
			$dataProvider->setActive($xml->ROR->SOR->SSU)
				->setFileNumber($xml->ROR->SZ->OV)
				->setCourt($xml->ROR->SZ->SD->T);
		}
	}

	private function createDataProvider()
	{
		return new DataProvider(new DataFactory());
	}

}
