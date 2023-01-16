<?php declare(strict_types=1);

namespace h4kuna\Ares\Data;

class Data implements \JsonSerializable
{
	public bool $active;

	public ?string $city = null;

	public ?string $company = null;

	public ?string $court = null;

	public \DateTimeImmutable $created;

	public ?\DateTimeImmutable $dissolved = null;

	public ?string $file_number = null;

	public ?string $city_district = null;

	public ?string $city_post = null;

	public ?string $court_all = null;

	public string $in;

	public bool $is_person;

	public int $legal_form_code;

	public ?string $house_number = null;

	public ?string $street = null;

	public ?string $tin = null;

	public bool $vat_payer;

	public ?string $zip = null;

	/**
	 * @var array<string>
	 */
	public array $nace = [];

	public string $psu = '';


	/**
	 * @param array<string, string|null> $map
	 * @return array<string, mixed>
	 */
	public function toArray(array $map = []): array
	{
		$data = $this->getData();
		if ($map === []) {
			return $data;
		}
		$mappedData = [];
		foreach ($map as $in => $out) {
			if (array_key_exists($in, $data)) {
				if ($out === null) {
					$out = $in;
				}

				$mappedData[$out] = $data[$in];
			}
		}

		return $mappedData;
	}


	/**
	 * @param SubjectFlag::* $index
	 */
	public function psu(int $index): string
	{
		return substr($this->psu, $index, 1);
	}


	/**
	 * @return array<string, scalar|array<string>>
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() /* mixed */
	{
		$data = $this->getData();
		$data['created'] = self::formatDate($this->created);

		if ($this->dissolved !== null) {
			$data['dissolved'] = self::formatDate($this->dissolved);
		}

		/** @var  array<string, scalar|array<string>> $data */
		return $data;
	}


	public function __toString()
	{
		return (string) json_encode($this->jsonSerialize());
	}


	/**
	 * @return array<string, mixed>
	 */
	private function getData(): array
	{
		$data = get_object_vars($this);
		assert(is_array($data));

		return $data;
	}


	private static function formatDate(\DateTimeInterface $date): string
	{
		return $date->format($date::RFC3339);
	}

}
