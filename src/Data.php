<?php

namespace h4kuna\Ares;

/**
 * @author Milan Matějček
 */
class Data implements IData, \ArrayAccess, \Iterator, \Countable
{

	private $data = [];

	/**
	 * @param bool $isActive
	 */
	public function setActive($isActive)
	{
		$this->data['active'] = $isActive;
	}

	public function setCity($city)
	{
		$this->data['city'] = $city;
	}

	public function setCompany($company)
	{
		$this->data['company'] = $company;
	}

	public function setCourt($court)
	{
		$this->data['court'] = $court;
	}

	public function setCreated(\DateTime $date)
	{
		$this->data['created'] = $date;
	}

	public function setFileNumber($fileNumber)
	{
		$this->data['file_number'] = $fileNumber;
	}

	public function setPerson($isPerson)
	{
		$this->data['person'] = $isPerson;
	}

	public function setStreet($street)
	{
		$this->data['street'] = $street;
	}

	public function setIN($in)
	{
		$this->data['in'] = $in;
	}

	public function setTIN($tin)
	{
		$this->data['tin'] = $tin;
	}

	public function setVatpay($vatPay)
	{
		$this->data['vat_pay'] = $vatPay;
	}

	public function setZip($zip)
	{
		$this->data['zip'] = $zip;
	}

	private function setFileNumberAndCourt()
	{
		if (!isset($this->data['court_all']) && array_key_exists('file_number', $this->data) && array_key_exists('court', $this->data)) {
			$this->data['court_all'] = $this->data['file_number'] . ', ' . $this->data['court'];
		}
	}

	/**
	 * Copy data
	 * @param array $map
	 * @return array
	 */
	public function toArray(array $map = [])
	{
		$this->setFileNumberAndCourt();
		if (!$map) {
			return $this->data;
		}
		$out = [];
		foreach ($map as $k => $v) {
			if ($this->offsetExists($k)) {
				if (!$v) {
					$v = $k;
				}
				$out[$v] = $this->data[$k];
			}
		}
		return $out;
	}

	public function __toString()
	{
		$data = $this->toArray();
		if ($data['created'] instanceof \DateTime) {
			$data['created'] = $data['created']->format(\DateTime::ISO8601);
		}
		return json_encode($data);
	}

	/**
	 * ARRAY-ACCESS INTERFACE **************************************************
	 * *************************************************************************
	 */

	/**
	 *
	 * @param string $offset
	 * @return bool
	 */
	public function offsetExists($offset)
	{
		return array_key_exists($offset, $this->data);
	}

	/**
	 * Return value from array
	 * @param string $offset
	 * @return string
	 * @throws DataOffsetDoesNotExists
	 */
	public function offsetGet($offset)
	{
		if ($offset === 'court_all') {
			$this->setFileNumberAndCourt();
		}
		if ($this->offsetExists($offset)) {
			return $this->data[$offset];
		}
		throw new DataOffsetDoesNotExists($offset);
	}

	/**
	 * @param string $offset
	 * @param string $value
	 * @return string
	 */
	public function offsetSet($offset, $value)
	{
		return $this->data[$offset] = $value;
	}

	/**
	 * Remove value from array
	 * @param string $offset
	 * @return void
	 */
	public function offsetUnset($offset)
	{
		unset($this->data[$offset]);
	}

	/**
	 * ITERATOR INTERFACE ******************************************************
	 * *************************************************************************
	 */

	/**
	 * Actual value
	 * @return void
	 */
	public function current()
	{
		return current($this->data);
	}

	/**
	 * Actual key of value
	 * @return string
	 */
	public function key()
	{
		return key($this->data);
	}

	/**
	 * Next value
	 * @return string
	 */
	public function next()
	{
		return next($this->data);
	}

	/** @retrun void */
	public function rewind()
	{
		reset($this->data);
	}

	/** @return bool */
	public function valid()
	{
		return array_key_exists($this->key(), $this->data);
	}

	/**
	 * COUNTABLE INTERFACE *****************************************************
	 * *************************************************************************
	 */

	/** @return int */
	public function count()
	{
		return count($this->data);
	}

}
