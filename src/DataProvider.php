<?php

namespace h4kuna\Ares;

use DateTime,
	DateTimeZone;

/**
 * @author Milan Matějček
 */
class DataProvider
{

	/** @var IDataFactory */
	private $dataFactory;

	/** @var array */
	private $data = [];


	public function __construct(IDataFactory $dataFactory)
	{
		$this->dataFactory = $dataFactory;
	}


	/** @return Data */
	public function getData()
	{
		if (is_array($this->data)) {
			$this->setFileNumberAndCourt();
			$this->data = $this->dataFactory->create($this->data);
		}
		return $this->data;
	}


	public function prepareData()
	{
		$this->data = [];
		return $this;
	}


	/**
	 * @param string|bool $active
	 * @return self
	 */
	public function setActive($active)
	{
		$this->data['active'] = is_bool($active) ? $active : (((string) $active) == 'Aktivní'); // ==
		return $this;
	}


	public function setCity($city)
	{
		$this->data['city'] = self::toNull($city);
		return $this;
	}


	public function setCompany($company)
	{
		$this->data['company'] = self::toNull($company);
		return $this;
	}


	public function setCourt($court)
	{
		$this->data['court'] = self::toNull($court);
		return $this;
	}


	public function setCreated($date)
	{
		$this->data['created'] = new DateTime((string) $date, new DateTimeZone('Europe/Prague'));
		return $this;
	}


	public function setFileNumber($fileNumber)
	{
		$this->data['file_number'] = self::toNull($fileNumber);
		return $this;
	}


	public function setIN($in)
	{
		$this->data['in'] = self::toNull($in);
		return $this;
	}


	public function setIsPerson($s)
	{
		$this->data['is_person'] = ((string) $s) <= '108';
		return $this;
	}


	private function setFileNumberAndCourt()
	{
		$this->data['court_all'] = null;
		if ($this->data['file_number'] && $this->data['court']) {
			$this->data['court_all'] = $this->data['file_number'] . ', ' . $this->data['court'];
		}
	}


	public function setCityDistrict($district)
	{
		$this->data['city_district'] = self::toNull($district);
		return $this;
	}


	public function setCityPost($district)
	{
		$this->data['city_post'] = self::toNull($district);
		return $this;
	}


	public function setStreet($street)
	{
		$this->data['street'] = self::toNull($street);
		return $this;
	}


	public function setHouseNumber($cd, $co)
	{
		$this->data['house_number'] = self::toNull(trim($cd . '/' . $co, '/'));
		return $this;
	}


	public function setTIN($s)
	{
		$tin = strval($s);
		$this->data['tin'] = self::toNull($tin);
		$this->data['vat_payer'] = (bool) $tin;
		return $this;
	}


	public function setZip($zip)
	{
		$this->data['zip'] = self::toNull($zip);
		return $this;
	}


	private static function toNull($v)
	{
		$string = trim((string) $v);
		if ($string === '') {
			return null;
		}
		return $string;
	}

}
