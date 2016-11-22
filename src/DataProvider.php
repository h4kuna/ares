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

	/** @var IData */
	private $data;

	public function __construct(IDataFactory $dataFactory)
	{
		$this->dataFactory = $dataFactory;
	}

	/** @return IData */
	public function getData()
	{
		return $this->data;
	}

	public function prepareData()
	{
		$this->setData($this->dataFactory->create());
		return $this;
	}

	/**
	 * @param string $s
	 * @return self
	 */
	public function setActive($s)
	{
		$this->data->setActive(strval($s) == 'Aktivní');
		return $this;
	}

	public function setCity($s)
	{
		$this->data->setCity(strval($s));
		return $this;
	}

	public function setCompany($s)
	{
		$this->data->setCompany(strval($s));
		return $this;
	}

	public function setCourt($s)
	{
		$this->data->setCourt(strval($s));
		return $this;
	}

	public function setCreated($s)
	{
		$this->data->setCreated(new DateTime($s, new DateTimeZone('Europe/Prague')));
		return $this;
	}

	public function setFileNumber($s)
	{
		$this->data->setFileNumber(strval($s));
		return $this;
	}

	public function setIN($s)
	{
		$this->data->setIn(strval($s));
		return $this;
	}

	public function setPerson($s)
	{
		$this->data->setPerson(strval($s) <= '108');
		return $this;
	}

	public function setStreet($uc, $nco, $co)
	{
		$street = strval($uc);
		if (is_numeric($street)) {
			$street = $nco . ' ' . $street;
		}

		if ($co) {
			$street .= '/' . $co;
		}

		$this->data->setStreet($street);
		return $this;
	}

	public function setTIN($s)
	{
		$tin = strval($s);
		$this->data->setTIN($tin);
		$this->data->setVatpayer((bool) $tin);
		return $this;
	}

	public function setZip($s)
	{
		$this->data->setZip(strval($s));
		return $this;
	}

	protected function setData(IData $data)
	{
		$this->data = $data;
	}

}
