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


	public function prepareData(): self
	{
		$this->data = [];
		return $this;
	}


	/**
	 * @param string|bool $active
	 * @return static
	 */
	public function setActive($active): self
	{
		$this->data['active'] = is_bool($active) ? $active : ($active === 'Aktivní');
		return $this;
	}


	public function setCity(string $city): self
	{
		$this->data['city'] = self::toNull($city);
		return $this;
	}


	public function setCompany(string $company): self
	{
		$this->data['company'] = self::toNull($company);
		return $this;
	}


	public function setCourt(string $court): self
	{
		$this->data['court'] = self::toNull($court);
		return $this;
	}


	public function setCreated(string $date): self
	{
		$this->data['created'] = new DateTime($date, new DateTimeZone('Europe/Prague'));
		return $this;
	}


	public function setFileNumber(string $fileNumber): self
	{
		$this->data['file_number'] = self::toNull($fileNumber);
		return $this;
	}


	public function setIN(string $in): self
	{
		$this->data['in'] = self::toNull($in);
		return $this;
	}


	public function setIsPerson(string $s): self
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


	public function setCityDistrict(string $district): self
	{
		$this->data['city_district'] = self::toNull($district);
		return $this;
	}


	public function setCityPost(string $district): self
	{
		$this->data['city_post'] = self::toNull($district);
		return $this;
	}


	public function setStreet(string $street): self
	{
		$this->data['street'] = self::toNull($street);
		return $this;
	}


	public function setHouseNumber(string $cd, string $co): self
	{
		$this->data['house_number'] = self::toNull(trim($cd . '/' . $co, '/'));
		return $this;
	}


	public function setTIN(string $s): self
	{
		$this->data['tin'] = self::toNull($s);
		$this->data['vat_payer'] = (bool) $s;
		return $this;
	}


	public function setZip(string $zip): self
	{
		$this->data['zip'] = self::toNull($zip);
		return $this;
	}


	public function setNace(array $nace): self
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

}
