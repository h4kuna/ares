<?php declare(strict_types=1);

namespace h4kuna\Ares;

use GuzzleHttp;
use h4kuna\Ares\Data\DataProviderFactory;
use h4kuna\Ares\Http\RequestProvider;

final class AresFactory
{

	public function create(): Ares
	{
		return new Ares($this->createRequestProviderFactory(), new DataProviderFactory());
	}


	protected function createRequestProviderFactory(): RequestProvider
	{
		if (!class_exists(GuzzleHttp\Client::class)) {
			throw new \RuntimeException('Guzzle not found, let implement own solution or install guzzle by: composer require guzzlehttp/guzzle');
		}
		$requestFactory = new GuzzleHttp\Psr7\HttpFactory();
		$client = new GuzzleHttp\Client();

		return new RequestProvider($requestFactory, $client, $requestFactory);
	}

}
