<?php declare(strict_types=1);

namespace h4kuna\Ares\Http;

use h4kuna\Ares\Exceptions\ConnectionException;
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


	public function oneIn(string $in): ResponseInterface
	{
		$request = $this->requestFactory->createRequest('GET', self::createUrl($in))
			->withHeader('X-Powered-By', 'h4kuna/ares');
		try {
			return $this->client->sendRequest($this->modifyOneRequest($request));
		} catch (ClientExceptionInterface $e) {
			throw new ConnectionException($e->getMessage(), $e->getCode(), $e);
		}
	}


	/**
	 * @param array<string>|array<int> $identificationNumbersBatch
	 */
	public function multiIn(array $identificationNumbersBatch): ResponseInterface
	{
		$request = $this->requestFactory->createRequest('POST', self::MULTI_IN)
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


	protected static function createUrl(string $inn): string
	{
		$parameters = [
			'ico' => $inn,
			'aktivni' => 'false',
		];

		return self::ONE_IN . '?' . http_build_query($parameters);
	}


	protected function modifyOneRequest(RequestInterface $request): RequestInterface
	{
		return $request;
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

}
