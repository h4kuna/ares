<?php declare(strict_types = 1);

namespace h4kuna\Ares;

use Generator;
use h4kuna\Ares\Adis\ContentProvider as AdisContentProvider;
use h4kuna\Ares\Ares\Client as AresClient;
use h4kuna\Ares\Ares\Core\ContentProvider as AresCoreContentProvider;
use h4kuna\Ares\Ares\Core\Data;
use h4kuna\Ares\DataBox\ContentProvider as DataBoxContentProvider;
use h4kuna\Ares\Exception\AdisResponseException;
use h4kuna\Ares\Exception\IdentificationNumberNotFoundException;
use h4kuna\Ares\Exception\ResultException;
use h4kuna\Ares\Exception\ServerResponseException;
use h4kuna\Ares\Vies\ContentProvider as ViesContentProvider;
use h4kuna\Ares\Vies\ViesEntity;
use stdClass;

/**
 * properties become readonly
 */
class Ares
{

	public function __construct(
		public AresCoreContentProvider $aresContentProvider,
		public DataBoxContentProvider $dataBoxContentProvider,
		public AdisContentProvider $adisContentProvider,
		public ViesContentProvider $viesContentProvider,
	)
	{
	}

	/**
	 * @deprecated use property
	 */
	public function getAdis(): AdisContentProvider
	{
		return $this->adisContentProvider;
	}

	public function getAresClient(): AresClient
	{
		return $this->aresContentProvider->getClient();
	}

	/**
	 * @param array<KeyName, string|int> $identificationNumbers
	 * @return Generator<(int&KeyName)|(KeyName&string), Data>
	 *
	 * @template KeyName
	 *
	 * @throws ResultException
	 * @throws ServerResponseException
	 */
	public function loadBasicMulti(array $identificationNumbers): Generator
	{
		return $this->aresContentProvider->loadByIdentificationNumbers($identificationNumbers);
	}

	/**
	 * @throws AdisResponseException
	 * @throws IdentificationNumberNotFoundException
	 * @throws ServerResponseException
	 */
	public function loadBasic(string $in): Data
	{
		return $this->aresContentProvider->load($in);
	}

	/**
	 * @return array<stdClass>
	 *
	 * @throws ResultException
	 * @throws ServerResponseException
	 */
	public function loadDataBox(string $in): array
	{
		return $this->dataBoxContentProvider->load($in);
	}

	/**
	 * @return object{countryCode: string, vatNumber: string, requestDate: string, valid: bool, requestIdentifier: string, name: string, address: string, traderName: string, traderStreet: string, traderPostalCode: string, traderCity: string, traderCompanyType: string, traderNameMatch: string, traderStreetMatch: string, traderPostalCodeMatch: string, traderCityMatch: string, traderCompanyTypeMatch: string}
	 *
	 * @throws ServerResponseException
	 */
	public function checkVatVies(string|ViesEntity $viesEntityOrTin): object
	{
		return $this->viesContentProvider->checkVat($viesEntityOrTin);
	}

}
