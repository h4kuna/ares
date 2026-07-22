<?php declare(strict_types = 1);

namespace h4kuna\Ares\Tool;

use h4kuna\Ares\Exception\ServerResponseException;
use JsonException;
use Nette\Utils\Json;
use Psr\Http\Message\ResponseInterface;
use SimpleXMLElement;
use stdClass;
use function get_debug_type;
use function simplexml_load_string;
use function sprintf;

final class Xml
{

	/**
	 * @throws ServerResponseException
	 */
	public static function toJson(SimpleXMLElement|ResponseInterface $response): stdClass
	{
		if ($response instanceof ResponseInterface) {
			$xml = @simplexml_load_string($response->getBody()->getContents());
		} else {
			$xml = $response;
		}
		if ($xml === false) {
			throw ServerResponseException::brokenXml();
		}

		try {
			$data = Json::decode(Json::encode($xml));
		} catch (JsonException $e) {
			throw ServerResponseException::fromException($e);
		}

		if ($data instanceof stdClass === false) {
			throw ServerResponseException::badResponse(sprintf('Expected XML convertible to object, got %s.', get_debug_type($data)));
		}

		return $data;
	}

}
