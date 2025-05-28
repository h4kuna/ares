<?php declare(strict_types=1);

namespace h4kuna\Ares\Tool;

use h4kuna\Ares\Exception\ServerResponseException;
use JsonException;
use Nette\Utils\Json;
use Psr\Http\Message\ResponseInterface;
use SimpleXMLElement;
use stdClass;

final class Xml
{
	public static function toJson(SimpleXMLElement|ResponseInterface $response): stdClass
	{
		if ($response instanceof ResponseInterface) {
			$xml = @simplexml_load_string($response->getBody()->getContents());
		} else {
			$xml = $response;
		}
		if ($xml === false) {
			throw new ServerResponseException();
		}

		try {
			$data = Json::decode(Json::encode($xml));
			assert($data instanceof stdClass);
		} catch (JsonException $e) {
			throw new ServerResponseException($e->getMessage(), (int) $e->getCode(), $e);
		}

		return $data;
	}

}
