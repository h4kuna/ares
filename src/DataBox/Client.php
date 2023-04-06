<?php declare(strict_types=1);

namespace h4kuna\Ares\DataBox;

use h4kuna\Ares\Exceptions\ConnectionException;
use h4kuna\Ares\Http\RequestProvider;
use Psr\Http\Message\StreamInterface;

/**
 * @see https://www.mojedatovaschranka.cz/sds/p/download/sds_webove_sluzby.pdf#view=Fit
 */
final class Client
{
	public function __construct(
		private RequestProvider $requestProvider,
	)
	{
	}


	public function request(StreamInterface $body): \stdClass
	{
		$request = $this->requestProvider->createXmlRequest('https://www.mojedatovaschranka.cz/sds/ws/call', $body);

		$xml = $this->requestProvider->xmlResponse($request);
		$data = RequestProvider::toJson($xml);

		if (isset($data->Message)) {
			throw new ConnectionException($data->Message);
		} elseif (!isset($data->Osoba)) {
			throw new ConnectionException('No content');
		}

		return $data;
	}

}
