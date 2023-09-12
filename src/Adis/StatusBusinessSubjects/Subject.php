<?php declare(strict_types=1);

namespace h4kuna\Ares\Adis\StatusBusinessSubjects;

use stdClass;

final class Subject
{
	public function __construct(
		public bool $exists,
		public string $type,
		public string $tin,
		public ?bool $reliable,
		public bool $isVatPayer,
		public string $taxOfficeNumber,
		public ?stdClass $address,
	)
	{
	}
}
