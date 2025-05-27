<?php declare(strict_types=1);

namespace h4kuna\Ares\DataBox;

use h4kuna\Ares\Exception\ResultException;
use h4kuna\Ares\Exception\ServerResponseException;
use h4kuna\Ares\Http\TransportProvider;
use h4kuna\Ares\Tool\Strings;
use h4kuna\Ares\Tool\Xml;
use Psr\Http\Message\StreamInterface;
use stdClass;

/**
 * @see https://www.mojedatovaschranka.cz/sds/p/download/sds_webove_sluzby.pdf#view=Fit
 */
final class Client
{
	public static string $url = 'https://www.mojedatovaschranka.cz/sds/ws/call';

	public function __construct(
		private TransportProvider $requestProvider,
	) {
	}

	/**
	 * @throws ResultException
	 * @throws ServerResponseException
	 */
	public function request(StreamInterface $body): stdClass
	{
		$request = $this->requestProvider->createXmlRequest(self::$url, $body);

		$response = $this->requestProvider->response($request);

		$data = Xml::toJson($response);

		if (isset($data->Message)) {
			throw ResultException::withMessage(Strings::fromMixedStrict($data->Message));
		} elseif (isset($data->Osoba) === false) {
			throw ServerResponseException::badResponse('No content');
		}

		return $data;
	}

}
