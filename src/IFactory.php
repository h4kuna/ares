<?php declare(strict_types=1);

namespace h4kuna\Ares;

use GuzzleHttp;

interface IFactory
{

	/** @return Data */
	function createData(array $data);


	/** @return GuzzleHttp\Client */
	function createGuzzleClient(array $curlOptions);


	/** @return DataProvider */
	function createDataProvider();

}
