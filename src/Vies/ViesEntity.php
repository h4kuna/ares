<?php declare(strict_types=1);

namespace h4kuna\Ares\Vies;

/**
 * @phpstan-type viesParam array{vatNumber: string,countryCode: string,requesterMemberStateCode: string,requesterNumber: string,traderName: string,traderStreet: string,traderPostalCode: string,traderCity: string,traderCompanyType: string}
 */
class ViesEntity
{
	public function __construct(
		private string $vatNumber,
		private string $countryCode,
		private string $requesterMemberStateCode = '',
		private string $requesterNumber = '',
		private string $traderName = '',
		private string $traderStreet = '',
		private string $traderPostalCode = '',
		private string $traderCity = '',
		private string $traderCompanyType = '',
	)
	{
	}


	/**
	 * @return viesParam
	 */
	public function toParam(): array
	{
		/** @var viesParam $data */
		$data = array_filter(get_object_vars($this));

		return $data;
	}
}
