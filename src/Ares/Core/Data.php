<?php declare(strict_types=1);

namespace h4kuna\Ares\Ares\Core;

use DateTimeImmutable;
use h4kuna\Ares\Adis\StatusBusinessSubjects\Subject;
use h4kuna\Ares\Ares\Sources;
use h4kuna\Ares\Tools\Strings;
use JsonSerializable;
use stdClass;
use Stringable;

/**
 * @phpstan-type DataType array<string, mixed>
 */
class Data implements JsonSerializable, Stringable
{
	public bool $active;

	public ?string $city;

	public ?string $company;

	public DateTimeImmutable $created;

	public ?DateTimeImmutable $dissolved;

	public ?string $city_district;

	public ?string $city_post;

	public string $in;

	public bool $is_person;

	public int $legal_form_code;

	public ?string $house_number;

	public ?string $street;

	/**
	 * <prefix>DIČ
	 * @todo https://github.com/h4kuna/ares/issues/30#issuecomment-1719170527
	 */
	public ?string $tin;

	public bool $vat_payer;

	public ?string $zip;

	public ?string $country;

	public ?string $country_code;

	/**
	 * @var array<string>
	 */
	public array $nace = [];

	/**
	 * @var array<Sources::SER*, true|string>
	 */
	public array $sources = [];

	public ?stdClass $original = null;

	public ?Subject $adis = null;


	public function setAdis(Subject $adis): void
	{
		if ($adis->exists) {
			$this->vat_payer = $adis->isVatPayer;
			$this->tin = $adis->tin;
		} else {
			$this->tin = null;
		}

		$this->adis = $adis;
	}


	/**
	 * @return array<string, scalar|array<string>>
	 */
	public function jsonSerialize(): mixed
	{
		$data = $this->toArray();
		$data['created'] = Strings::exportDate($this->created);

		if ($this->dissolved !== null) {
			$data['dissolved'] = Strings::exportDate($this->dissolved);
		}

		/** @var  array<string, scalar|array<string>> $data */
		return $data;
	}


	public function __toString()
	{
		return (string) json_encode($this);
	}


	/**
	 * @return DataType
	 */
	public function __serialize(): array
	{
		return $this->toArray();
	}


	/**
	 * @param DataType $data
	 */
	public function __unserialize(array $data): void
	{
		foreach ($data as $name => $value) {
			$this->$name = $value;
		}
	}


	/**
	 * @return DataType
	 */
	public function toArray(): array
	{
		$data = get_object_vars($this);
		unset($data['original'], $data['adis']);

		return $data;
	}

}
