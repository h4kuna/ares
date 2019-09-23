<?php declare(strict_types=1);

namespace h4kuna\Ares;

use h4kuna\Ares\Exceptions\ConnectionException;
use h4kuna\Ares\Exceptions\IdentificationNumberNotFoundException;

class Ares
{

	public const URL = 'https://wwwinfo.mfcr.cz/cgi-bin/ares/darv_bas.cgi';

	/** @var IFactory */
	private $factory;

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
	public function loadData(string $in, array $options = []): Data
	{
		$this->loadXML($in, $options);
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
	private function loadXML(string $in, array $options)
	{
		$client = $this->factory->createGuzzleClient($options);
		try {
			$xmlSource = $client->request('GET', $this->createUrl($in))->getBody()->getContents();
		} catch (\Throwable $e) {
			throw new ConnectionException($e->getMessage(), $e->getCode(), $e);
		}
		$xml = @simplexml_load_string($xmlSource);
		if (!$xml) {
			throw new ConnectionException('No xml from ARES. IN ' . $in);
		}

		$ns = $xml->getDocNamespaces();
		$answer = $xml->children($ns['are'])->children($ns['D']);
		$this->parseErrorAnswer($answer, $in);
		$this->processXml($answer->VBAS, $this->getDataProvider()->prepareData());
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
			->setCreated((string) $xml->DV)
			->setNace(self::existsArray($xml->Nace, 'NACE'))
			->setActive(!isset($xml->DZ));

		if (isset($xml->ROR)) {
			$dataProvider
				->setFileNumber((string) $xml->ROR->SZ->OV)
				->setCourt((string) $xml->ROR->SZ->SD->T);
		} else {
			$dataProvider
				->setFileNumber('')
				->setCourt('');
		}
	}


	private function createUrl(string $inn): string
	{
		$parameters = [
			'ico' => $inn,
			'aktivni' => 'false',
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
	
	
	private static function existsArray(\SimpleXMLElement $element, string $property): array
	{
		return isset($element->{$property}) ? ((array) $element->{$property}) : [];
	}


	private function parseErrorAnswer(\SimpleXMLElement $answer, string $in): void
	{
		if (isset($answer->VBAS)) {
			return;
		}
		$error = trim((string) $answer->E->ET);
		// 61 - subject disappeared
		// 71 - not exists
		throw new IdentificationNumberNotFoundException($error, $in);
	}

}
