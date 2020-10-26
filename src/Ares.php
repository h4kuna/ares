<?php declare(strict_types=1);

namespace h4kuna\Ares;

use h4kuna\Ares\Exceptions\ConnectionException;
use h4kuna\Ares\Exceptions\IdentificationNumberNotFoundException;

class Ares
{

	public const URL = 'https://wwwinfo.mfcr.cz/cgi-bin/ares/darv_bas.cgi';
	public const POST_URL = 'https://wwwinfo.mfcr.cz/cgi-bin/ares/xar.cgi';

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
	 * @param array $array
	 * @param array $options
	 * @return array
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 * @throws \Exception
	 */
	public function loadPostData(array $array, $options = []): array {
		$client = $client = $this->factory->createGuzzleClient($options);
		$offset = 0;
		$output = [];

		while (count(array_slice($array, $offset)) > 0) {
			$slice = array_slice($array, $offset,100, TRUE);
			$offset += 100;

			$response = $client->request('POST', self::POST_URL, [
				'headers' => [
					'Content-type' => 'application/xml'
				],
				'body' => $this->createBodyContent($slice)
			]);


			$simpleXml = simplexml_load_string($response->getBody()->getContents(), null, 0, 'SOAP-ENV', true);
			$simpleXml->registerXPathNamespace('SOAP-ENV', 'http://schemas.xmlsoap.org/soap/envelope/');


			$responseData = $simpleXml->children('SOAP-ENV', true)
				->Body
				->children('are', true)
				->children('are', true);

			foreach ($responseData as $item) {
				$D = $item->children('D' , true);
				$pid = (int) $D->PID->__toString();

				try {
					if($D->E->asXML() !== FALSE) {
						$DE = $D->E->children('D', TRUE);
						throw new IdentificationNumberNotFoundException($DE->ET->__toString(), $DE->EK->__toString());
					}

					$this->processXml($D->VBAS, $this->getDataProvider()->prepareData());

					$output[$pid] = $this->getData();
				} catch (IdentificationNumberNotFoundException $exception) {
					$output[$pid] = [
						'error_code' => $exception->getCode(),
						'error_msg' => $exception->getMessage()
					];
				}
			}
		}

		return $output;
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
		throw new IdentificationNumberNotFoundException(sprintf('IN "%s". %s', $in, $errorMessage), (int) $errorCode);
	}


	private static function xmlValue(\SimpleXMLElement $xml, string $xpath): ?string
	{
		$result = $xml->xpath($xpath);
		if ($result === []) {
			return null;
		}
		return trim((string) $result[0]);
	}


	/**
	 * @param array $items
	 * @return string
	 * @throws \Exception
	 */
	private function createBodyContent(array $items): string {
		$date = new \DateTime();
		$content = '
		<are:Ares_dotazy 
		xmlns:are="http://wwwinfo.mfcr.cz/ares/xml_doc/schemas/ares/ares_request_orrg/v_1.0.0" 
		xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
		xsi:schemaLocation="http://wwwinfo.mfcr.cz/ares/xml_doc/schemas/ares/ares_request_orrg/v_1.0.0 http://wwwinfo.mfcr.cz/ares/xml_doc/schemas/ares/ares_request_orrg/v_1.0.0/ares_request_orrg.xsd" 
		dotaz_datum_cas="' . $date->format('Y-m-dTH:i:s') . '" 
		dotaz_pocet="' . count($items) . '" 
		dotaz_typ="Basic" 
		vystup_format="XML" 
		validation_XSLT="http://wwwinfo.mfcr.cz/ares/xml_doc/schemas/ares/ares_answer/v_1.0.0/ares_answer.xsl" 
		answerNamespaceRequired="http://wwwinfo.mfcr.cz/ares/xml_doc/schemas/ares/ares_answer_basic/v_1.0.3"
		Id="Ares_dotaz">
		';

		foreach ($items as $key => $ic) {
			$content .=  '<Dotaz><Pomocne_ID>' . $key . '</Pomocne_ID><ICO>' . $ic . '</ICO></Dotaz>';
		}

		$content .= '</are:Ares_dotazy>';
		return $content;
	}
}
