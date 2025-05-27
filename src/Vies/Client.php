<?php declare(strict_types=1);

namespace h4kuna\Ares\Vies;

use h4kuna\Ares\Exception\ServerResponseException;
use h4kuna\Ares\Http\TransportProvider;
use stdClass;

/**
 * @phpstan-type ViesResponse object{countryCode: string, vatNumber: string, requestDate: string, valid: bool, requestIdentifier: string, name: string, address: string, traderName: string, traderStreet: string, traderPostalCode: string, traderCity: string, traderCompanyType: string, traderNameMatch: string, traderStreetMatch: string, traderPostalCodeMatch: string, traderCityMatch: string, traderCompanyTypeMatch: string}&\stdClass
 */
final class Client
{
	public static string $url = 'https://ec.europa.eu/taxation_customs/vies/rest-api';


	public function __construct(private TransportProvider $transportProvider)
	{
	}


	/**
	 * @return ViesResponse
	 *
	 * @throws ServerResponseException
	 */
	public function checkVatNumber(ViesEntity $viesEntity): object
	{
		$request = $this->transportProvider->createJsonRequest(static::$url . '/check-vat-number', $viesEntity->toParam());
		$response = $this->transportProvider->response($request);

		$data = $this->transportProvider->toJson($response);
		if (isset($data->errorWrappers[0]->error) && $data->errorWrappers[0] instanceof stdClass) {
			if (isset($data->errorWrappers[0]->message)) {
				throw new ServerResponseException(sprintf('%s: %s', $data->errorWrappers[0]->error, $data->errorWrappers[0]->message));
			}

			throw new ServerResponseException($data->errorWrappers[0]->error);
		}

		/** @var ViesResponse $data */
		return $data;
	}

	/**
	 * @throws ServerResponseException
	 */
	public function status(): stdClass
	{
		$request = $this->transportProvider->createRequest(static::$url . '/check-status');
		$response = $this->transportProvider->response($request);

		return $this->transportProvider->toJson($response);
	}
}
