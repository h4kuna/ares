<?php declare(strict_types=1);

namespace h4kuna\Ares;

use DateTime;
use DateTimeZone;

class DataProvider
{

	/** @var IFactory */
	private $factory;

	/** @var array */
	private $data;

	/** @var Data */
	private $dataMessenger;


	public function __construct(IFactory $dataFactory)
	{
		$this->factory = $dataFactory;
	}


	public function getData(): Data
	{
		if (is_array($this->data)) {
			$this->setFileNumberAndCourt();
			$this->dataMessenger = $this->factory->createData($this->data);
		}
		return $this->dataMessenger;
	}


	/**
	 * @return static
	 */
	public function prepareData()
	{
		$this->data = [];
		return $this;
	}


	/**
	 * @return static
	 */
	public function setActive(bool $active)
	{
		$this->data['active'] = $active;
		return $this;
	}


	/**
	 * @return static
	 */
	public function setCity(string $city)
	{
		$this->data['city'] = self::toNull($city);
		return $this;
	}


	/**
	 * @return static
	 */
	public function setCompany(string $company)
	{
		$this->data['company'] = self::toNull($company);
		return $this;
	}


	/**
	 * @return static
	 */
	public function setCourt(string $court)
	{
		$this->data['court'] = self::toNull($court);
		return $this;
	}


	/**
	 * @return static
	 */
	public function setCreated(string $date)
	{
		$this->data['created'] = self::createDateTime($date);
		return $this;
	}


	/**
	 * @return static
	 */
	public function setDissolved(?string $date)
	{
		if ($date === null) {
			$this->setActive(true);
			$this->data['dissolved'] = null;
		} else {
			$this->data['dissolved'] = self::createDateTime($date);
			$this->setActive(false);
		}

		return $this;
	}


	/**
	 * @return static
	 */
	public function setFileNumber(string $fileNumber)
	{
		$this->data['file_number'] = self::toNull($fileNumber);
		return $this;
	}


	/**
	 * @return static
	 */
	public function setIN(string $in)
	{
		$this->data['in'] = self::toNull($in);
		return $this;
	}


	/**
	 * @return static
	 */
	public function setIsPerson(string $s)
	{
		$this->data['is_person'] = $s <= '108' || $s === '424' || $s === '425';
		$this->data['legal_form_code'] = (int) $s;
		return $this;
	}


	private function setFileNumberAndCourt(): void
	{
		$this->data['court_all'] = null;
		if ($this->data['file_number'] && $this->data['court']) {
			$this->data['court_all'] = $this->data['file_number'] . ', ' . $this->data['court'];
		}
	}


	/**
	 * @return static
	 */
	public function setCityDistrict(string $district)
	{
		$this->data['city_district'] = self::toNull($district);
		return $this;
	}


	/**
	 * @return static
	 */
	public function setCityPost(string $district)
	{
		$this->data['city_post'] = self::toNull($district);
		return $this;
	}


	/**
	 * @return static
	 */
	public function setStreet(string $street)
	{
		$this->data['street'] = self::toNull($street);
		return $this;
	}


	/**
	 * @return static
	 */
	public function setHouseNumber(string $cd, string $co, string $ca)
	{
		$houseNumber = self::toNull(trim($cd . '/' . $co, '/'));
		if ($houseNumber === null) {
			$houseNumber = self::toNull($ca);
		}

		$this->data['house_number'] = $houseNumber;
		return $this;
	}


	/**
	 * @return static
	 */
	public function setTIN(string $s)
	{
		$this->data['tin'] = self::toNull($s);
		$this->data['vat_payer'] = (bool) $s;
		return $this;
	}


	/**
	 * @return static
	 */
	public function setZip(string $zip)
	{
		$this->data['zip'] = self::toNull($zip);
		return $this;
	}


	/**
	 * @return static
	 */
	public function setNace(array $nace)
	{
		$newNace = [];
		foreach ($nace as $item) {
			$newNace[] = (string) $item;
		}
		$this->data['nace'] = $newNace;
		return $this;
	}


	private static function toNull(string $v): ?string
	{
		$string = trim($v);
		if ($string === '') {
			return null;
		}
		return $string;
	}


	private static function createDateTime(string $date): DateTime
	{
		return new DateTime($date, new DateTimeZone('Europe/Prague'));
	}

}
