<?php declare(strict_types=1);

namespace h4kuna\Ares\Adis;

use h4kuna\Ares\Adis\Soap\Envelope;
use h4kuna\Ares\Exceptions\ServerResponseException;
use h4kuna\Ares\Http\TransportProvider;
use h4kuna\Ares\Tools\Xml;
use stdClass;

final class Client
{
	public static string $url = 'https://adisrws.mfcr.cz/adistc/axis2/services/rozhraniCRPDPH.rozhraniCRPDPHSOAP';


	public function __construct(private TransportProvider $transportProvider,)
	{
	}


	/**
	 * @param array<string, string> $chunk
	 * @return array<stdClass>
	 */
	public function statusBusinessSubjects(array $chunk): array
	{
		$xml = Envelope::StatusNespolehlivySubjektRozsireny(...$chunk);
		$data = $this->request($xml, 'StatusNespolehlivySubjektRozsirenyResponse');
		$attributes = '@attributes';
		assert($data->status instanceof stdClass);
		if (isset($data->status->$attributes) === false) {
			throw new ServerResponseException('Broken response xml.');
		}
		$element = $data->status->$attributes;
		assert($element instanceof stdClass);

		if ($element->statusCode !== '0') {
			throw new ServerResponseException($element->statusText, (int) $element->statusCode);
		}

		return is_array($data->statusSubjektu) ? $data->statusSubjektu : [$data->statusSubjektu];
	}


	private function request(string $xml, string $name): stdClass
	{
		$request = $this->transportProvider->createXmlRequest(self::$url, $xml);
		$response = $this->transportProvider->response($request);
		$xml = @simplexml_load_string($response->getBody()->getContents(), namespace_or_prefix: 'soapenv', is_prefix: true);

		if ($xml === false || isset($xml->Body->children()->$name) === false) {
			throw new ServerResponseException(sprintf('Missing tag "%s" in response.', $name));
		}

		return Xml::toJson($xml->Body->children()->$name);
	}

}
