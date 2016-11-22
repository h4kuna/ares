<?php

namespace h4kuna\Ares;

interface IData
{

	/**
	 * @param bool $active
	 */
	function setActive($active);

	/**
	 * @param string $city
	 */
	function setCity($city);

	/**
	 * @param string $company
	 */
	function setCompany($company);

	/**
	 * @param string $court
	 */
	function setCourt($court);

	/**
	 * @param \DateTime $date
	 */
	function setCreated(\DateTime $date);

	/**
	 * @param string $fileNumber
	 */
	function setFileNumber($fileNumber);

	/**
	 * @param string $in
	 */
	function setIN($in);

	/**
	 * @param bool $isPerson
	 */
	function setPerson($isPerson);

	/**
	 * @param string $street
	 */
	function setStreet($street);

	/**
	 * @param string $tin
	 */
	function setTIN($tin);

	/**
	 * @var bool $isVatPay
	 */
	function setVatpayer($isVatPayer);

	/**
	 * @param string $zip
	 */
	function setZip($zip);
}
