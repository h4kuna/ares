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


	public function createGuzzleClient(array $options = [CURLOPT_CONNECTTIMEOUT => 30]): GuzzleHttp\Client
	{
		return new GuzzleHttp\Client([
			'curl' => $options,
			'headers' => ['X-Powered-By' => 'h4kuna/ares'],
			'verify' => __DIR__ . '/../cert/cacert.pem',
		]);
	}


	public function createBodyFactory(): BodyFactory
	{
		return new BodyFactory();
	}

}
