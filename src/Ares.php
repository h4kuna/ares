<?php declare(strict_types=1);

namespace h4kuna\Ares;

use h4kuna\Ares\Exceptions\ConnectionException;
use h4kuna\Ares\Exceptions\IdentificationNumberNotFoundException;

class Ares
{

	const URL = 'http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_bas.cgi';

	/** @var IFactory */
	private $factory;

	/** @var bool */
	private $activeMode;

	/** @var DataProvider */
	private $dataProvider;


	public function __construct(IFactory $factory = null)
	{
		if ($factory === null) {
			$factory = new Factory();
		}
		$this->factory = $factory;
	}


	/**
	 * Load fresh data.
	 * @throws IdentificationNumberNotFoundException
	 */
	public function loadData(string $in): Data
	{
		try {
			$this->loadXML($in, true);
		} catch (IdentificationNumberNotFoundException $e) {
			$this->loadXML($in, false);
		}
		return $this->getData();
	}


	/**
	 * Get temporary data.
	 */
	public function getData(): Data
	{
		return $this->getDataProvider()->getData();
	}


	/**
	 * Load XML and fill Data object
	 * @throws IdentificationNumberNotFoundException
	 */
	private function loadXML(string $in, bool $activeOnly)
	{
		$client = $this->factory->createGuzzleClient();
		try {
			$xmlSource = $client->request('GET', $this->createUrl($in, $activeOnly))->getBody()->getContents();
		} catch (\Exception $e) {
			throw new ConnectionException($e->getMessage(), $e->getCode(), $e);
		}
		$xml = @simplexml_load_string($xmlSource);
		if (!$xml) {
			throw new IdentificationNumberNotFoundException($in);
		}

		$ns = $xml->getDocNamespaces();
		$xmlEl = $xml->children($ns['are'])->children($ns['D'])->VBAS;

		if (!isset($xmlEl->ICO)) {
			throw new IdentificationNumberNotFoundException($in);
		}

		$this->processXml($xmlEl, $this->getDataProvider()->prepareData());
	}


	protected function processXml(\SimpleXMLElement $xml, DataProvider $dataProvider): void
	{
		$dataProvider->setIN((string) $xml->ICO)
			->setTIN((string) $xml->DIC)
			->setCompany((string) $xml->OF)
			->setZip(self::exists($xml->AA, 'PSC'))
			->setStreet(self::exists($xml->AA, 'NU'))
			->setCity(self::exists($xml->AA, 'N'))
			->setHouseNumber(self::exists($xml->AA, 'CD'), self::exists($xml->AA, 'CO'))
			->setCityPost(self::exists($xml->AA, 'NMC'))
			->setCityDistrict(self::exists($xml->AA, 'NCO'))
			->setIsPerson(self::exists($xml->PF, 'KPF'))
			->setCreated((string) $xml->DV);

		if (isset($xml->ROR)) {
			$dataProvider->setActive((string) $xml->ROR->SOR->SSU)
				->setFileNumber((string) $xml->ROR->SZ->OV)
				->setCourt((string) $xml->ROR->SZ->SD->T);
		} else {
			$dataProvider->setActive($this->activeMode)
				->setFileNumber('')
				->setCourt('');
		}
		if (!$this->isActiveMode()) {
			$dataProvider->setActive(false);
		}
	}


	protected function isActiveMode(): bool
	{
		return $this->activeMode === true;
	}


	private function createUrl(string $inn, bool $activeOnly): string
	{
		$this->activeMode = $activeOnly;
		$parameters = [
			'ico' => $inn,
			'aktivni' => $activeOnly ? 'true' : 'false',
		];
		return self::URL . '?' . http_build_query($parameters);
	}


	private function getDataProvider(): DataProvider
	{
		if ($this->dataProvider === null) {
			$this->dataProvider = $this->factory->createDataProvider();
		}
		return $this->dataProvider;
	}


	private static function exists(\SimpleXMLElement $element, string $property): string
	{
		return isset($element->{$property}) ? ((string) $element->{$property}) : '';
	}

}
