<?php

namespace h4kuna\Ares;

use GuzzleHttp;

interface IFactory
{

	/** @return Data */
	function createData(array $data);


	/** @return GuzzleHttp\Client */
	function createGuzzleClient();


	/** @return DataProvider */
	function createDataProvider();

}
