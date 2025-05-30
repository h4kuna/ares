<?php declare(strict_types=1);

namespace h4kuna\Ares\Adis;

use h4kuna\Ares\Adis\Soap\Envelope;
use h4kuna\Ares\Exception\ServerResponseException;
use h4kuna\Ares\Http\TransportProvider;
use h4kuna\Ares\Tool\Arrays;
use h4kuna\Ares\Tool\Integer;
use h4kuna\Ares\Tool\Strings;
use h4kuna\Ares\Tool\Xml;
use SimpleXMLElement;
use stdClass;

final class Client
{
	public static string $url = 'https://adisrws.mfcr.cz/adistc/axis2/services/rozhraniCRPDPH.rozhraniCRPDPHSOAP';

	public function __construct(
		private TransportProvider $transportProvider,
	) {
	}


	/**
	 * @param array<string, string> $chunk
	 * @return list<stdClass>
	 *
	 * @throws ServerResponseException
	 */
	public function statusBusinessSubjects(array $chunk): array
	{
		$xml = Envelope::StatusNespolehlivySubjektRozsireny(...$chunk);
		$data = $this->request($xml, 'StatusNespolehlivySubjektRozsirenyResponse');
		$attributes = '@attributes';

		if (
			$data->status instanceof stdClass === false
			|| isset($data->status->$attributes) === false
			|| $data->status->$attributes instanceof stdClass === false
		) {
			throw ServerResponseException::brokenXml();
		}
		$element = $data->status->$attributes;
		if ($element->statusCode !== '0') {
			throw ServerResponseException::badResponse(Strings::fromMixedStrict($element->statusText), (int) Integer::fromMixed($element->statusCode));
		}

		return Arrays::fromStdClass($data->statusSubjektu);
	}

	/**
	 * @throws ServerResponseException
	 */
	private function request(string $xml, string $name): stdClass
	{
		$request = $this->transportProvider->createXmlRequest(self::$url, $xml);
		$response = $this->transportProvider->response($request);
		$xml = @simplexml_load_string($response->getBody()->getContents(), namespace_or_prefix: 'soapenv', is_prefix: true);

		if ($xml === false || ($xml->Body->children()->$name instanceof SimpleXMLElement) === false) {
			throw ServerResponseException::badResponse(sprintf('Missing tag "%s" in response.', $name));
		}

		return Xml::toJson($xml->Body->children()->$name);
	}

}
