<?php declare(strict_types=1);

namespace h4kuna\Ares\Http;

use h4kuna\Ares\Exceptions\ConnectionException;
use Nette\Utils\Json;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

final class RequestProvider
{
	public function __construct(
		private RequestFactoryInterface $requestFactory,
		private ClientInterface $client,
	)
	{
	}


	public function xmlResponse(RequestInterface|string $url): \SimpleXMLElement
	{
		$request = $url instanceof RequestInterface ? $url : $this->createRequest($url);
		try {
			$response = $this->client->sendRequest($request);
		} catch (ClientExceptionInterface $e) {
			throw new ConnectionException($e->getMessage(), $e->getCode(), $e);
		}

		$xml = @simplexml_load_string($response->getBody()->getContents());
		if ($xml === false) {
			throw new ConnectionException();
		}

		return $xml;
	}


	public function createRequest(string $url, string $method = 'GET'): RequestInterface
	{
		return $this->requestFactory->createRequest($method, $url)
			->withHeader('X-Powered-By', 'h4kuna/ares');
	}


	public function createXmlRequest(string $url, StreamInterface $body): RequestInterface
	{
		return $this->createRequest($url, 'POST')
			->withHeader('Content-Type', 'application/xml; charset=utf-8')
			->withBody($body);
	}


	public static function toJson(\SimpleXMLElement $answer): \stdClass
	{
		try {
			$data = Json::decode(Json::encode($answer));
			assert($data instanceof \stdClass);
		} catch (\JsonException $e) {
			throw new ConnectionException($e->getMessage(), $e->getCode(), $e);
		}

		return $data;
	}

}
