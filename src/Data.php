<?php

namespace h4kuna\Ares;

use h4kuna\DataType\Immutable;

/**
 * @author Milan Matějček
 * @property-read bool $active
 * @property-read string $city
 * @property-read string $city_district
 * @property-read string $city_post
 * @property-read string $company
 * @property-read string $court
 * @property-read string $court_all
 * @property-read \DateTime $created
 * @property-read string $file_number
 * @property-read string $in
 * @property-read bool $is_person
 * @property-read string $house_number
 * @property-read string $street
 * @property-read string $tin
 * @property-read string $vat_payer
 * @property-read string $zip
 */
class Data extends Immutable\Messenger
{

	/**
	 * Copy data
	 * @param array $map
	 * @return array
	 */
	public function toArray(array $map = [])
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
		if ($this->created instanceof \DateTime) {
			$data['created'] = $this->created->format(\DateTime::ISO8601);
		}
		return $data;
	}


	public function __toString()
	{
		return json_encode($this->jsonSerialize());
	}
}
