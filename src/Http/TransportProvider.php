<?php declare(strict_types=1);

namespace h4kuna\Ares\Http;

use h4kuna\Ares\Exception\ServerResponseException;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use stdClass;

final class TransportProvider
{
	public function __construct(
		private RequestFactoryInterface $requestFactory,
		private ClientInterface $client,
		private StreamFactoryInterface $streamFactory,
	)
	{
	}


	public function response(RequestInterface|string $url): ResponseInterface
	{
		$request = $url instanceof RequestInterface ? $url : $this->createRequest($url);
		try {
			$response = $this->client->sendRequest($request);
		} catch (ClientExceptionInterface $e) {
			throw new ServerResponseException($e->getMessage(), (int) $e->getCode(), $e);
		}

		return $response;
	}


	public function toJson(ResponseInterface $response): stdClass
	{
		try {
			$json = Json::decode($response->getBody()->getContents());
			assert($json instanceof stdClass);
		} catch (JsonException $e) {
			throw new ServerResponseException($e->getMessage(), (int) $e->getCode(), $e);
		}

		return $json;
	}


	public function createRequest(string $url, string $method = 'GET'): RequestInterface
	{
		return $this->requestFactory->createRequest($method, $url)
			->withHeader('X-Powered-By', 'h4kuna/ares');
	}


	/**
	 * @param array<string, mixed> $data
	 */
	public function createJsonRequest(string $url, array $data = []): RequestInterface
	{
		$request = $this->createPost($url, 'application/json');
		if ($data !== []) {
			$request = $request->withBody($this->streamFactory->createStream(Json::encode($data)));
		}

		return $request;
	}


	public function createXmlRequest(string $url, string|StreamInterface $body): RequestInterface
	{
		if (is_string($body)) {
			$body = $this->streamFactory->createStream($body);
		}

		return $this->createPost($url, 'application/xml')
			->withBody($body);
	}


	private function createPost(string $url, string $contentType): RequestInterface
	{
		return $this->createRequest($url, 'POST')
			->withHeader('Content-Type', "$contentType; charset=utf-8");
	}

}
