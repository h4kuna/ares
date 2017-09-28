<?php

namespace h4kuna\Ares;

interface IDataFactory
{

	/** @return Data */
	function create(array $data);
}
