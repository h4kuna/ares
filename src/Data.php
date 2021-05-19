<?php declare(strict_types=1);

namespace h4kuna\Ares;

use h4kuna\DataType\Immutable;

/**
 * @property-read bool $active
 * @property-read string $city
 * @property-read string $city_district
 * @property-read string $city_post
 * @property-read string $company
 * @property-read string $court
 * @property-read string $court_all
 * @property-read \DateTime $created
 * @property-read \DateTime $dissolved
 * @property-read string $file_number
 * @property-read string $in
 * @property-read bool $is_person
 * @property-read int $legal_form_code
 * @property-read string $house_number
 * @property-read string $street
 * @property-read string $tin
 * @property-read bool $vat_payer
 * @property-read string $zip
 * @property-read array $nace
 */
class Data extends Immutable\Messenger
{

	public function toArray(array $map = []): array
	{
		if ($map === []) {
			return $this->getData();
		}
		$out = [];
		foreach ($map as $k => $v) {
			if ($this->offsetExists($k)) {
				if (!$v) {
					$v = $k;
				}
				$out[$v] = $this[$k];
			}
		}
		return $out;
	}


	public function jsonSerialize()
	{
		$data = $this->getData();
		if ($this->created instanceof \DateTimeInterface) {
			$data['created'] = self::formatDate($this->created);
		}

		if ($this->dissolved instanceof \DateTimeInterface) {
			$data['dissolved'] = self::formatDate($this->dissolved);
		}
		return $data;
	}


	public function __toString()
	{
		return (string) json_encode($this->jsonSerialize());
	}


	private static function formatDate(\DateTimeInterface $date): string
	{
		return $date->format(\DateTime::ISO8601);
	}

}
