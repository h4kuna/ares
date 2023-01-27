<?php declare(strict_types=1);

namespace h4kuna\Ares\Http;

use h4kuna\Ares\Exceptions\ConnectionException;
use h4kuna\Ares\Exceptions\IdentificationNumberNotFoundException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

class RequestProvider
{
	private const BASE_URL = 'https://wwwinfo.mfcr.cz/cgi-bin/ares';
	protected const ONE_IN = self::BASE_URL . '/darv_bas.cgi';
	protected const ONE_BL = self::BASE_URL . '/darv_or.cgi';
	protected const MULTI_IN = self::BASE_URL . '/xar.cgi';

	private RequestFactoryInterface $requestFactory;

	private ClientInterface $client;

	private StreamFactoryInterface $streamFactory;


	public function __construct(
		RequestFactoryInterface $requestFactory,
		ClientInterface $client,
		StreamFactoryInterface $streamFactory
	)
	{
		$this->requestFactory = $requestFactory;
		$this->client = $client;
		$this->streamFactory = $streamFactory;
	}


	/**
	 * @throws IdentificationNumberNotFoundException
	 */
	public function businessList(string $in): \SimpleXMLElement
	{
		$parameters = [
			'ico' => $in,
		];

		$url = self::ONE_BL . '?' . http_build_query($parameters);

		return $this->xmlResponse($url, $in);
	}


	/**
	 * @throws IdentificationNumberNotFoundException
	 */
	public function basic(string $in): \SimpleXMLElement
	{
		$parameters = [
			'ico' => $in,
			'aktivni' => 'false',
		];

		$url = self::ONE_IN . '?' . http_build_query($parameters);

		return $this->xmlResponse($url, $in);
	}


	/**
	 * @throws IdentificationNumberNotFoundException
	 */
	protected function xmlResponse(string $url, string $in): \SimpleXMLElement
	{
		$request = $this->createRequest($url);
		try {
			$response = $this->client->sendRequest($request);
		} catch (ClientExceptionInterface $e) {
			throw new ConnectionException($e->getMessage(), $e->getCode(), $e);
		}

		$xml = @simplexml_load_string($response->getBody()->getContents());
		if ($xml === false) {
			throw new ConnectionException();
		}

		$ns = $xml->getDocNamespaces();
		if (!isset($ns['are']) || !isset($ns['D'])) {
			throw new ConnectionException();
		}
		self::parseErrorAnswer($xml, $in);

		return $xml->children($ns['are'])->children($ns['D']);
	}


	private function createRequest(string $url, string $method = 'GET'): RequestInterface
	{
		return $this->requestFactory->createRequest($method, $url)
			->withHeader('X-Powered-By', 'h4kuna/ares');
	}


	/**
	 * @param array<string>|array<int> $identificationNumbersBatch
	 */
	public function basicMulti(array $identificationNumbersBatch): ResponseInterface
	{
		$request = $this->createRequest(self::MULTI_IN, 'POST')
			->withHeader('Content-type', 'application/xml')
			->withBody(
				$this->streamFactory->createStream(
					$this->createBodyContent($identificationNumbersBatch),
				),
			);

		try {
			return $this->client->sendRequest($request);
		} catch (ClientExceptionInterface $e) {
			throw new ConnectionException($e->getMessage(), $e->getCode(), $e);
		}
	}


	/**
	 * @param array<string>|array<int> $identificationNumbers
	 */
	private function createBodyContent(array $identificationNumbers): string
	{
		$date = date('Y-m-dTH:i:s');
		$countIn = count($identificationNumbers);
		$content = <<<BODY
		<are:Ares_dotazy 
		xmlns:are="http://wwwinfo.mfcr.cz/ares/xml_doc/schemas/ares/ares_request_orrg/v_1.0.0" 
		xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
		xsi:schemaLocation="http://wwwinfo.mfcr.cz/ares/xml_doc/schemas/ares/ares_request_orrg/v_1.0.0 http://wwwinfo.mfcr.cz/ares/xml_doc/schemas/ares/ares_request_orrg/v_1.0.0/ares_request_orrg.xsd" 
		dotaz_datum_cas="$date" 
		dotaz_pocet="$countIn" 
		dotaz_typ="Basic" 
		vystup_format="XML" 
		validation_XSLT="http://wwwinfo.mfcr.cz/ares/xml_doc/schemas/ares/ares_answer/v_1.0.0/ares_answer.xsl" 
		answerNamespaceRequired="http://wwwinfo.mfcr.cz/ares/xml_doc/schemas/ares/ares_answer_basic/v_1.0.3"
		Id="Ares_dotaz">
		BODY;

		foreach ($identificationNumbers as $key => $in) {
			$content .= "<Dotaz><Pomocne_ID>$key</Pomocne_ID><ICO>$in</ICO></Dotaz>";
		}

		return $content . '</are:Ares_dotazy>';
	}


	private static function parseErrorAnswer(\SimpleXMLElement $answer, string $in): void
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

}
