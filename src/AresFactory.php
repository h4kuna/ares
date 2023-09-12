<?php declare(strict_types=1);

namespace h4kuna\Ares;

use GuzzleHttp;
use h4kuna\Ares\Adis\StatusBusinessSubjects\StatusBusinessSubjectsTransformer;
use h4kuna\Ares\DataBox;
use h4kuna\Ares\Exceptions\InvalidStateException;
use h4kuna\Ares\Http\HttpFactory;
use h4kuna\Ares\Http\TransportProvider;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class AresFactory
{
	private null|GuzzleHttp\Psr7\HttpFactory|HttpFactory $httpFactory = null;


	public function create(): Ares
	{
		$streamFactory = $this->createStreamFactory();
		$transportProvider = $this->createTransportProvider($streamFactory);
		$adisContentProvider = $this->adisContentProvider($transportProvider);
		$aresClient = new Ares\Client($transportProvider);

		$dataBoxClient = new DataBox\Client($transportProvider);
		$dataBoxContentProvider = new DataBox\ContentProvider($dataBoxClient, $streamFactory);

		return new Ares($aresClient, $dataBoxContentProvider, $adisContentProvider);
	}


	public function createRequestFactory(): RequestFactoryInterface
	{
		self::checkGuzzle();

		return $this->httpFactory ??= class_exists(GuzzleHttp\Psr7\HttpFactory::class) ? new GuzzleHttp\Psr7\HttpFactory() : new HttpFactory();
	}


	public function createClient(): ClientInterface
	{
		self::checkGuzzle();

		return new GuzzleHttp\Client();
	}


	public function createStreamFactory(): StreamFactoryInterface
	{
		$factory = $this->createRequestFactory();
		assert($factory instanceof StreamFactoryInterface);

		return $factory;
	}


	protected function adisContentProvider(TransportProvider $transportProvider): Adis\ContentProvider
	{
		return new Adis\ContentProvider(new Adis\Client($transportProvider), new StatusBusinessSubjectsTransformer());
	}


	public function createTransportProvider(StreamFactoryInterface $streamFactory): TransportProvider
	{
		$client = $this->createClient();
		$requestFactory = $this->createRequestFactory();
		return new TransportProvider($requestFactory, $client, $streamFactory);
	}


	private static function checkGuzzle(): void
	{
		if (!class_exists(GuzzleHttp\Client::class)) {
			throw new InvalidStateException('Guzzle not found, let implement own solution or install guzzle by: composer require guzzlehttp/guzzle');
		}
	}

}
