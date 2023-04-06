<?php declare(strict_types=1);

namespace h4kuna\Ares;

use GuzzleHttp;
use h4kuna\Ares\Basic;
use h4kuna\Ares\DataBox\Client;
use h4kuna\Ares\Http\AresRequestProvider;
use h4kuna\Ares\Http\RequestProvider;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class AresFactory
{
	private ?GuzzleHttp\Psr7\HttpFactory $httpFactory = null;


	public function create(): Ares
	{
		$requestFactory = $this->createRequestFactory();
		$client = $this->createClient();
		$streamFactory = $this->createStreamFactory();
		$requestProvider = new RequestProvider($requestFactory, $client);
		$aresContentProvider = new AresRequestProvider($requestProvider, $streamFactory, $client);

		$basicContent = new Basic\ContentProvider(new Basic\DataProviderFactory(), $aresContentProvider);
		$businessListProvider = new BusinessList\ContentProvider($aresContentProvider);

		$dataBoxClient = new Client($requestProvider);
		$dataBoxContentProvider = new DataBox\ContentProvider($dataBoxClient, $streamFactory);

		return new Ares($basicContent, $businessListProvider, $dataBoxContentProvider);
	}


	protected function createRequestFactory(): RequestFactoryInterface
	{
		self::checkGuzzle();

		return $this->httpFactory ??= new GuzzleHttp\Psr7\HttpFactory();
	}


	protected function createClient(): ClientInterface
	{
		self::checkGuzzle();

		return new GuzzleHttp\Client();
	}


	protected function createStreamFactory(): StreamFactoryInterface
	{
		$factory = $this->createRequestFactory();
		assert($factory instanceof GuzzleHttp\Psr7\HttpFactory);

		return $factory;
	}


	private static function checkGuzzle(): void
	{
		if (!class_exists(GuzzleHttp\Client::class)) {
			throw new \RuntimeException('Guzzle not found, let implement own solution or install guzzle by: composer require guzzlehttp/guzzle');
		}
	}

}
