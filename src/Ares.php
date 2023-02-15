<?php declare(strict_types=1);

namespace h4kuna\Ares;

use h4kuna\Ares\Basic;
use h4kuna\Ares\Exceptions\IdentificationNumberNotFoundException;

class Ares
{
	public const RESULT_FAILED = Basic\ContentProvider::RESULT_FAILED;
	public const RESULT_SUCCESS = Basic\ContentProvider::RESULT_SUCCESS;


	public function __construct(
		private Basic\ContentProvider $basicContentProvider,
		private BusinessList\ContentProvider $businessListContentProvider,
	)
	{
	}


	/**
	 * @param array<string|int> $identificationNumbers
	 * @return array{failed: array<Error>, success: array<Basic\Data>}
	 * @deprecated use loadBasicMulti()
	 */
	public function loadByIdentificationNumbers(array $identificationNumbers): array
	{
		return $this->basicContentProvider->loadByIdentificationNumbers($identificationNumbers);
	}


	/**
	 * @param array<string|int> $identificationNumbers
	 * @return array{failed: array<Error>, success: array<Basic\Data>}
	 */
	public function loadBasicMulti(array $identificationNumbers): array
	{
		return $this->basicContentProvider->loadByIdentificationNumbers($identificationNumbers);
	}


	/**
	 * @throws IdentificationNumberNotFoundException
	 * @deprecated use loadBasic()
	 */
	public function loadData(string $in): Basic\Data
	{
		return $this->basicContentProvider->load($in);
	}


	/**
	 * @throws IdentificationNumberNotFoundException
	 */
	public function loadBasic(string $in): Basic\Data
	{
		return $this->basicContentProvider->load($in);
	}


	/**
	 * @throws IdentificationNumberNotFoundException
	 */
	public function loadBusinessList(string $in): \stdClass
	{
		return $this->businessListContentProvider->load($in)->Vypis_OR;
	}

}
