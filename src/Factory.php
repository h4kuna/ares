<?php declare(strict_types=1);

namespace h4kuna\Ares;

use GuzzleHttp;

class Factory implements IFactory
{

	public function createDataProvider()
	{
		return new DataProvider($this);
	}


	public function createData(array $data)
	{
		return new Data($data);
	}


	public function createGuzzleClient(array $curlOptions)
	{
	    	if (!isset($curlOptions[CURLOPT_CONNECTTIMEOUT])) {
		    	$curlOptions[CURLOPT_CONNECTTIMEOUT] = 30;
		}
		return new GuzzleHttp\Client(['curl' => $curlOptions]);
	}

}
