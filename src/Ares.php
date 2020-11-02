<?php declare(strict_types=1);

namespace h4kuna\Ares;

use h4kuna\Ares\Exceptions\ConnectionException;
use h4kuna\Ares\Exceptions\IdentificationNumberNotFoundException;
use GuzzleHttp;

class Ares
{
	public const RESULT_FAILED = 'failed';
	public const RESULT_SUCCESS = 'success';
	public const URL = 'https://wwwinfo.mfcr.cz/cgi-bin/ares/darv_bas.cgi';
	public const POST_URL = 'https://wwwinfo.mfcr.cz/cgi-bin/ares/xar.cgi';
	private const POST_IDENTIFICATION_NUMBERS_LIMIT = 100; // in one post request can be max 100 identification numbers

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
	 * @param array<string>|array<int> $identificationNumbers
	 * @param array<string, string|int|float> $options
	 * @return array{failed: array<Error>, success: array<Data>}
	 */
	public function loadByIdentificationNumbers(array $identificationNumbers, array $options = []): array
	{
		$client = $this->factory->createGuzzleClient($options);
		$offset = 0;
		$output = [
			self::RESULT_FAILED => [],
			self::RESULT_SUCCESS => [],
		];

		$identificationNumbersCount = count($identificationNumbers);
		while (($identificationNumbersCount - $offset) > 0) {
			$identificationNumbersBatch = array_slice($identificationNumbers, $offset, self::POST_IDENTIFICATION_NUMBERS_LIMIT, true);
			$offset += self::POST_IDENTIFICATION_NUMBERS_LIMIT;

			$responseData = $this->sendDoseOfInsRequest($client, $identificationNumbersBatch);

			foreach ($responseData as $item) {
				$D = $item->children('D', true);
				$pid = (int) $D->PID->__toString();

				try {
					if ($D->E->asXML() !== false) {
						$DE = $D->E->children('D', true);
						throw new IdentificationNumberNotFoundException(trim($DE->ET->__toString()), $DE->EK->__toString());
					}

					$this->processXml($D->VBAS, $this->getDataProvider()->prepareData());

					$output[self::RESULT_SUCCESS][$pid] = $this->getData();
				} catch (IdentificationNumberNotFoundException $exception) {
					$output[self::RESULT_FAILED][$pid] = new Error((string) $identificationNumbers[$pid], $exception->getCode(), $exception->getMessage());
				}
			}
		}

		return $output;
	}


	/**
	 * Load fresh data.
	 * @param array<string, mixed> $options
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
	 * @param array<string, mixed> $options
	 * @throws IdentificationNumberNotFoundException
	 */
	private function loadXML(string $in, array $options): void
	{
		$client = $this->factory->createGuzzleClient($options);
		try {
			$xmlSource = $client->request('GET', $this->createUrl($in))
				->getBody()
				->getContents();
		} catch (\Throwable $e) {
			throw new ConnectionException($e->getMessage(), $e->getCode(), $e);
		}
		$xml = @simplexml_load_string($xmlSource);
		if (!$xml) {
			throw new ConnectionException();
		}

		$ns = $xml->getDocNamespaces();
		$answer = $xml->children($ns['are'])->children($ns['D']);
		$this->parseErrorAnswer($xml, $in);
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
			->setHouseNumber(self::exists($xml->AA, 'CD'), self::exists($xml->AA, 'CO'), self::exists($xml->AA, 'CA'))
			->setCityPost(self::exists($xml->AA, 'NMC'))
			->setCityDistrict(self::exists($xml->AA, 'NCO'))
			->setIsPerson(self::exists($xml->PF, 'KPF'))
			->setCreated((string) $xml->DV)
			->setNace(self::existsArray($xml->Nace, 'NACE'));

		$dataProvider->setDissolved(isset($xml->DZ) ? (string) $xml->DZ : null);

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
		$errorMessage = self::xmlValue($answer, '//D:ET[1]');
		$errorCode = self::xmlValue($answer, '//D:EK[1]');
		if ($errorMessage === null && $errorCode === null) {
			return;
		}

		// 61 - subject disappeared
		// 71 - not exists
		if (empty($errorMessage)) {
			throw new ConnectionException();
		}
		throw new IdentificationNumberNotFoundException(sprintf('IN "%s", Error: #%s, %s', $in, $errorCode, $errorMessage), $in);
	}


	private static function xmlValue(\SimpleXMLElement $xml, string $xpath): ?string
	{
		$result = $xml->xpath($xpath);
		if ($result === false || !isset($result[0])) {
			return null;
		}
		return trim((string) $result[0]);
	}


	/**
	 * @param array<string>|array<int> $identificationNumbersBatch
	 */
	public function sendDoseOfInsRequest(
		GuzzleHttp\Client $client,
		array $identificationNumbersBatch
	): \SimpleXMLElement
	{
		try {
			$body = $client->request('POST', self::POST_URL, [
				'headers' => [
					'Content-type' => 'application/xml',
				],
				'body' => $this->factory->createBodyFactory()->createBodyContent($identificationNumbersBatch),
			])->getBody()->getContents();
		} catch (\Throwable $e) {
			throw new ConnectionException($e->getMessage(), $e->getCode(), $e);
		}

		$simpleXml = @simplexml_load_string($body, "SimpleXMLElement", 0, 'SOAP-ENV', true);
		if ($simpleXml === false) {
			throw new ConnectionException();
		}
		$simpleXml->registerXPathNamespace('SOAP-ENV', 'http://schemas.xmlsoap.org/soap/envelope/');

		return $simpleXml->children('SOAP-ENV', true)
			->Body
			->children('are', true)
			->children('are', true);
	}

}
