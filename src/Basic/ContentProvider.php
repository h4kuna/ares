<?php declare(strict_types=1);

namespace h4kuna\Ares\Basic;

use h4kuna\Ares\Error;
use h4kuna\Ares\Exceptions\ConnectionException;
use h4kuna\Ares\Exceptions\IdentificationNumberNotFoundException;
use h4kuna\Ares\Http\AresRequestProvider;

final class ContentProvider
{
	public const RESULT_FAILED = 'failed';
	public const RESULT_SUCCESS = 'success';

	private const POST_IDENTIFICATION_NUMBERS_LIMIT = 100; // in one post request can be max 100 identification numbers


	public function __construct(
		private DataProviderFactory $dataProviderFactory,
		private AresRequestProvider $requestProvider,
	)
	{
	}


	/**
	 * @param array<string|int> $identificationNumbers
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
	public function load(string $in): Data
	{
		return $this->loadXML($in)->getData();
	}


	/**
	 * Load XML and fill Data object
	 * @throws IdentificationNumberNotFoundException
	 */
	private function loadXML(string $in): DataProvider
	{
		$answer = $this->requestProvider->basic($in);

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
			->setNace(self::existsArray($xml->Nace, 'NACE'))
			->setPSU((string) $xml->PSU);

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
		return isset($element->$property) ? ((string) $element->$property) : '';
	}


	/**
	 * @return array<string|int>
	 */
	private static function existsArray(\SimpleXMLElement $element, string $property): array
	{
		return isset($element->$property) ? ((array) $element->$property) : [];
	}


	/**
	 * @param array<string>|array<int> $identificationNumbersBatch
	 */
	private function sendDoseOfInsRequest(array $identificationNumbersBatch): \SimpleXMLElement
	{
		$body = $this->requestProvider->basicMulti($identificationNumbersBatch)
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
