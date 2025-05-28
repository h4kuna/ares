<?php declare(strict_types=1);

namespace h4kuna\Ares;

use Generator;
use h4kuna\Ares\Ares\Core;
use h4kuna\Ares\Ares\Core\Data;
use h4kuna\Ares\Exception\AdisResponseException;
use h4kuna\Ares\Exception\IdentificationNumberNotFoundException;
use h4kuna\Ares\Exception\ServerResponseException;
use h4kuna\Ares\Vies\ViesEntity;
use stdClass;

/**
 * properties become readonly
 */
class Ares
{
	public function __construct(
		public Core\ContentProvider $aresContentProvider,
		public DataBox\ContentProvider $dataBoxContentProvider,
		public Adis\ContentProvider $adisContentProvider,
		public Vies\ContentProvider $viesContentProvider,
	)
	{
	}


	/**
	 * @deprecated use property
	 */
	public function getAdis(): Adis\ContentProvider
	{
		return $this->adisContentProvider;
	}


	public function getAresClient(): Ares\Client
	{
		return $this->aresContentProvider->getClient();
	}


	/**
	 * @template KeyName
	 * @param array<KeyName, string|int> $identificationNumbers
	 * @return Generator<(int&KeyName)|(KeyName&string), Data>
	 */
	public function loadBasicMulti(array $identificationNumbers): Generator
	{
		return $this->aresContentProvider->loadByIdentificationNumbers($identificationNumbers);
	}


	/**
	 * @throws IdentificationNumberNotFoundException
	 * @throws AdisResponseException
	 */
	public function loadBasic(string $in): Data
	{
		return $this->aresContentProvider->load($in);
	}


	/**
	 * @return array<stdClass>
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
