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


	public function prepareData()
	{
		$this->data = [];
		return $this;
	}


	public function setActive(bool $active)
	{
		$this->data['active'] = $active;
		return $this;
	}


	public function setCity(string $city)
	{
		$this->data['city'] = self::toNull($city);
		return $this;
	}


	public function setCompany(string $company)
	{
		$this->data['company'] = self::toNull($company);
		return $this;
	}


	public function setCourt(string $court)
	{
		$this->data['court'] = self::toNull($court);
		return $this;
	}


	public function setCreated(string $date)
	{
		$this->data['created'] = self::createDateTime($date);
		return $this;
	}


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


	public function setFileNumber(string $fileNumber)
	{
		$this->data['file_number'] = self::toNull($fileNumber);
		return $this;
	}


	public function setIN(string $in)
	{
		$this->data['in'] = self::toNull($in);
		return $this;
	}


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


	public function setCityDistrict(string $district)
	{
		$this->data['city_district'] = self::toNull($district);
		return $this;
	}


	public function setCityPost(string $district)
	{
		$this->data['city_post'] = self::toNull($district);
		return $this;
	}


	public function setStreet(string $street)
	{
		$this->data['street'] = self::toNull($street);
		return $this;
	}


	public function setHouseNumber(string $cd, string $co)
	{
		$this->data['house_number'] = self::toNull(trim($cd . '/' . $co, '/'));
		return $this;
	}


	public function setTIN(string $s)
	{
		$this->data['tin'] = self::toNull($s);
		$this->data['vat_payer'] = (bool) $s;
		return $this;
	}


	public function setZip(string $zip)
	{
		$this->data['zip'] = self::toNull($zip);
		return $this;
	}


	public function setNace(array $nace)
	{
		$this->data['nace'] = $nace;
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
