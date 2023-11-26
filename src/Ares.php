<?php declare(strict_types=1);

namespace h4kuna\Ares;

use Generator;
use h4kuna\Ares\Ares\Core\Data;
use h4kuna\Ares\Exceptions\IdentificationNumberNotFoundException;
use h4kuna\Memoize\MemoryStorage;
use stdClass;

class Ares
{
	use MemoryStorage;

	public function __construct(
		private Ares\Client $aresClient,
		private DataBox\ContentProvider $dataBoxContentProvider,
		private Adis\ContentProvider $adisContentProvider,
	)
	{
	}


	public function getAdis(): Adis\ContentProvider
	{
		return $this->adisContentProvider;
	}


	public function getAresClient(): Ares\Client
	{
		return $this->aresClient;
	}


	/**
	 * @template KeyName
	 * @param array<KeyName, string|int> $identificationNumbers
	 * @return Generator<(int&KeyName)|(KeyName&string), Data>
	 */
	public function loadBasicMulti(array $identificationNumbers): Generator
	{
		return $this->aresContentProviderCache()->loadByIdentificationNumbers($identificationNumbers);
	}


	/**
	 * @throws IdentificationNumberNotFoundException
	 */
	public function loadBasic(string $in): Data
	{
		return $this->aresContentProviderCache()->load($in);
	}


	/**
	 * @return array<stdClass>
	 */
	public function loadDataBox(string $in): array
	{
		return $this->dataBoxContentProvider->load($in);
	}


	protected function aresContentProvider(): Ares\Core\ContentProvider
	{
		return new Ares\Core\ContentProvider(new Ares\Core\JsonToDataTransformer(), $this->getAresClient(), $this->adisContentProvider);
	}


	private function aresContentProviderCache(): Ares\Core\ContentProvider
	{
		return $this->memoize(__METHOD__, fn () => $this->aresContentProvider());
	}

}
