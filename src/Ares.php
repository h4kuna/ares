<?php declare(strict_types=1);

namespace h4kuna\Ares;

use h4kuna\Ares\Data\Data;
use h4kuna\Ares\Data\DataProvider;
use h4kuna\Ares\Data\DataProviderFactory;
use h4kuna\Ares\Exceptions\ConnectionException;
use h4kuna\Ares\Exceptions\IdentificationNumberNotFoundException;
use h4kuna\Ares\Http\RequestProvider;

class Ares
{
	public const RESULT_FAILED = 'failed';
	public const RESULT_SUCCESS = 'success';

	private const POST_IDENTIFICATION_NUMBERS_LIMIT = 100; // in one post request can be max 100 identification numbers

	protected RequestProvider $requestProvider;

	protected DataProviderFactory $dataProviderFactory;


	public function __construct(RequestProvider $requestProvider, DataProviderFactory $dataProviderFactory)
	{
		$this->requestProvider = $requestProvider;
		$this->dataProviderFactory = $dataProviderFactory;
	}


	/**
	 * @param array<string>|array<int> $identificationNumbers
	 * @return array{failed: array<Error>, success: array<Data>}
	 */
	public function loadByIdentificationNumbers(array $identificationNumbers): array
	{
		$offset = 0;
		$output = [
			self::RESULT_FAILED => [],
			self::RESULT_SUCCESS => [],
		];

		$identificationNumbersCount = count($identificationNumbers);
		while (($identificationNumbersCount - $offset) > 0) {
			$identificationNumbersBatch = array_slice($identificationNumbers, $offset, self::POST_IDENTIFICATION_NUMBERS_LIMIT, true);
			$offset += self::POST_IDENTIFICATION_NUMBERS_LIMIT;

			$responseData = $this->sendDoseOfInsRequest($identificationNumbersBatch);

			foreach ($responseData as $item) {
				$D = $item->children('D', true);
				$pid = (int) $D->PID->__toString();

				try {
					if ($D->E->asXML() !== false) {
						$DE = $D->E->children('D', true);
						throw new IdentificationNumberNotFoundException(trim($DE->ET->__toString()), $DE->EK->__toString());
					}

					$provider = $this->dataProviderFactory->create();
					$this->processXml($D->VBAS, $provider);

					$output[self::RESULT_SUCCESS][$pid] = $provider->getData();
				} catch (IdentificationNumberNotFoundException $exception) {
					$output[self::RESULT_FAILED][$pid] = new Error((string) $identificationNumbers[$pid], $exception->getCode(), $exception->getMessage());
				}
			}
		}

		return $output;
	}


	/**
	 * @throws IdentificationNumberNotFoundException
	 */
	public function loadData(string $in): Data
	{
		return $this->loadXML($in)->getData();
	}


	/**
	 * Load XML and fill Data object
	 * @throws IdentificationNumberNotFoundException
	 */
	private function loadXML(string $in): DataProvider
	{
		$xmlSource = $this->requestProvider->oneIn($in)
			->getBody()
			->getContents();

		$xml = @simplexml_load_string($xmlSource);
		if (!$xml) {
			throw new ConnectionException();
		}

		$ns = $xml->getDocNamespaces();
		if (!isset($ns['are']) || !isset($ns['D'])) {
			throw new ConnectionException();
		}

		$answer = $xml->children($ns['are'])->children($ns['D']);
		$this->parseErrorAnswer($xml, $in);
		$provider = $this->dataProviderFactory->create();
		$this->processXml($answer->VBAS, $provider);

		return $provider;
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


	private static function exists(?\SimpleXMLElement $element, string $property): string
	{
		return isset($element->{$property}) ? ((string) $element->{$property}) : '';
	}


	/**
	 * @return array<string|int>
	 */
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
		if ($errorMessage === '') {
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
	public function sendDoseOfInsRequest(array $identificationNumbersBatch): \SimpleXMLElement
	{
		$body = $this->requestProvider->multiIn($identificationNumbersBatch)
			->getBody()
			->getContents();

		$simpleXml = @simplexml_load_string($body, \SimpleXMLElement::class, 0, 'SOAP-ENV', true);
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
