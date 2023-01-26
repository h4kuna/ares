<?php declare(strict_types=1);

namespace h4kuna\Ares;

use h4kuna\Ares\Basic;
use h4kuna\Ares\Exceptions\IdentificationNumberNotFoundException;

class Ares
{
	public const RESULT_FAILED = Basic\ContentProvider::RESULT_FAILED;
	public const RESULT_SUCCESS = Basic\ContentProvider::RESULT_SUCCESS;

	protected Basic\ContentProvider $contentProvider;


	public function __construct(Basic\ContentProvider $contentProvider)
	{
		$this->contentProvider = $contentProvider;
	}


	/**
	 * @param array<string|int> $identificationNumbers
	 * @return array{failed: array<Error>, success: array<Basic\Data>}
	 */
	public function loadByIdentificationNumbers(array $identificationNumbers): array
	{
		return $this->contentProvider->loadByIdentificationNumbers($identificationNumbers);
	}


	/**
	 * @throws IdentificationNumberNotFoundException
	 * @deprecated use loadBasic()
	 */
	public function loadData(string $in): Basic\Data
	{
		return $this->contentProvider->loadBasic($in);
	}


	/**
	 * @throws IdentificationNumberNotFoundException
	 */
	public function loadBasic(string $in): Basic\Data
	{
		return $this->contentProvider->loadBasic($in);
	}

}
