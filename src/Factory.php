<?php declare(strict_types=1);

namespace h4kuna\Ares;

use GuzzleHttp;

class Factory implements IFactory
{

	public function createDataProvider(): DataProvider
	{
		return new DataProvider($this);
	}


	public function createData(array $data): Data
	{
		return new Data($data);
	}


	public function createGuzzleClient(): GuzzleHttp\Client
	{
		return new GuzzleHttp\Client(['curl' => [CURLOPT_CONNECTTIMEOUT => 30]]);
	}

}
