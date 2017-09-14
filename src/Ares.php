<?php

namespace h4kuna\Ares;

use GuzzleHttp;

/**
 * @author Milan Matějček <milan.matejcek@gmail.com>
 */
class Ares
{

	const URL = 'http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_bas.cgi';

	/** @var DataProvider */
	private $dataProvider;

	/** @var bool */
	private $activeMode;

	public function __construct(DataProvider $dataProvider = NULL)
	{
		if ($dataProvider === NULL) {
			$dataProvider = $this->createDataProvider();
		}
		$this->dataProvider = $dataProvider;
	}

	/**
	 * Load fresh data.
	 * @param int|string $in
	 * @return Data
	 * @throws IdentificationNumberNotFoundException
	 */
	public function loadData($in)
	{
		try {
			$this->loadXML((string) $in, TRUE);
		} catch (IdentificationNumberNotFoundException $e) {
			$this->loadXML((string) $in, FALSE);
		}
		return $this->getData();
	}

	/**
	 * Get temporary data.
	 * @return Data
	 */
	public function getData()
	{
		return $this->dataProvider->getData();
	}

	/**
	 * Load XML and fill Data object
	 * @param string $in
	 * @param bool $activeOnly
	 * @throws IdentificationNumberNotFoundException
	 */
	private function loadXML($in, $activeOnly)
	{
		$client = new GuzzleHttp\Client();
		$xmlSource = $client->request('GET', $this->createUrl($in, $activeOnly))->getBody();
		$xml = @simplexml_load_string($xmlSource);
		if (!$xml) {
			throw new IdentificationNumberNotFoundException($in);
		}

		$ns = $xml->getDocNamespaces();
		$xmlEl = $xml->children($ns['are'])->children($ns['D'])->VBAS;

		if (!isset($xmlEl->ICO)) {
			throw new IdentificationNumberNotFoundException($in);
		}

		$this->processXml($xmlEl, $this->dataProvider->prepareData());
	}

	protected function processXml($xml, DataProvider $dataProvider)
	{
		$dataProvider->setIN($xml->ICO)
			->setTIN($xml->DIC)
			->setCompany($xml->OF)
			->setZip(self::exists($xml->AA, 'PSC'))
			->setStreet(self::exists($xml->AA, 'NU'))
			->setCity(self::exists($xml->AA, 'N'))
			->setHouseNumber(self::exists($xml->AA, 'CD'), self::exists($xml->AA, 'CO'))
			->setCityPost(self::exists($xml->AA, 'NMC'))
			->setCityDistrict(self::exists($xml->AA, 'NCO'))
			->setIsPerson(self::exists($xml->PF, 'KPF'))
			->setCreated($xml->DV);

		if (isset($xml->ROR)) {
			$dataProvider->setActive($xml->ROR->SOR->SSU)
				->setFileNumber($xml->ROR->SZ->OV)
				->setCourt($xml->ROR->SZ->SD->T);
		} else {
			$dataProvider->setActive($this->activeMode)
				->setFileNumber('')
				->setCourt('');
		}
		if (!$this->isActiveMode()) {
			$dataProvider->setActive('no');
		}
	}

	protected function isActiveMode()
	{
		return $this->activeMode === TRUE;
	}

	private function createUrl($inn, $activeOnly)
	{
		$this->activeMode = (bool) $activeOnly;
		$parameters = [
			'ico' => $inn,
			'aktivni' => $activeOnly ? 'true' : 'false'
		];
		return self::URL . '?' . http_build_query($parameters);
	}

	private function createDataProvider()
	{
		return new DataProvider(new DataFactory());
	}

	private static function exists($element, $property)
	{
		return isset($element->{$property}) ? $element->{$property} : '';
	}

}
