<?php declare(strict_types=1);

namespace h4kuna\Ares;

use GuzzleHttp;

interface IFactory
{

	function createData(array $data): Data;


	function createGuzzleClient(): GuzzleHttp\Client;


	function createDataProvider(): DataProvider;

}
