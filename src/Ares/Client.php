<?php declare(strict_types=1);

namespace h4kuna\Ares\Ares;

use h4kuna\Ares\Exceptions\IdentificationNumberNotFoundException;
use h4kuna\Ares\Exceptions\ResultException;
use h4kuna\Ares\Exceptions\ServerResponseException;
use h4kuna\Ares\Http\TransportProvider;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Psr\Http\Message\ResponseInterface;
use stdClass;

class Client
{

	public function __construct(
		private TransportProvider $transportProvider,
	)
	{
	}


	/**
	 * @param Sources::SERVICE_*|Sources::CORE|Sources::DIAL $key
	 * @param array<string, mixed> $data
	 */
	public function searchEndpoint(string $key, array $data = []): stdClass
	{
		$request = $this->transportProvider->createJsonRequest(Helper::prepareUrlSearch($key), $data);
		$response = $this->transportProvider->response($request);

		return $this->responseToStdClass($response);
	}


	/**
	 * @param Sources::SERVICE_*|Sources::CORE $key
	 */
	public function useEndpoint(string $key, string $in): stdClass
	{
		$request = $this->transportProvider->createRequest(Helper::prepareUrl($key, $in));
		$response = $this->transportProvider->response($request);

		try {
			$json = $this->responseToStdClass($response);
		} catch (ResultException $e) {
			throw new IdentificationNumberNotFoundException(sprintf("Api: %s. %s", $key, $e->getMessage()), $in, $e);
		}

		return $json;
	}


	protected function responseToStdClass(ResponseInterface $response): stdClass
	{
		$json = $this->transportProvider->toJson($response);

		if ($response->getStatusCode() !== 200) {
			throw new ResultException(sprintf('%s%s: %s.', $json->kod ?? 0, isset($json->subKod) ? " ($json->subKod)" : '', $json->popis ?? ''));
		}

		return $json;
	}

}
