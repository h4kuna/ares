<?php declare(strict_types=1);

namespace h4kuna\Ares;

use GuzzleHttp;
use h4kuna\Ares\Adis\StatusBusinessSubjects\StatusBusinessSubjectsTransformer;
use h4kuna\Ares\Exceptions\InvalidStateException;
use h4kuna\Ares\Http\HttpFactory;
use h4kuna\Ares\Http\TransportProvider;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * @phpstan-type multiFactory RequestFactoryInterface&StreamFactoryInterface
 */
class AresFactory
{
	/**
	 * @var multiFactory|null
	 */
	private null|RequestFactoryInterface|StreamFactoryInterface $multiFactory = null;


	public function __construct(
		private ?ClientInterface $client = null,
		private ?StreamFactoryInterface $streamFactory = null,
		private ?RequestFactoryInterface $requestFactory = null,
	)
	{
	}


	public function create(): Ares
	{
		$streamFactory = $this->getStreamFactory();
		$transportProvider = $this->createTransportProvider($streamFactory);
		$adisContentProvider = $this->createAdisContentProvider($transportProvider);
		$aresClient = new Ares\Client($transportProvider);

		$dataBoxClient = new DataBox\Client($transportProvider);
		$dataBoxContentProvider = new DataBox\ContentProvider($dataBoxClient, $streamFactory);

		return new Ares($aresClient, $dataBoxContentProvider, $adisContentProvider);
	}


	public function getRequestFactory(): RequestFactoryInterface
	{
		return $this->requestFactory ??= $this->getMultiFactory();
	}


	public function getClient(): ClientInterface
	{
		if ($this->client !== null) {
			return $this->client;
		}
		self::checkGuzzle();

		return $this->client = new GuzzleHttp\Client();
	}


	public function getStreamFactory(): StreamFactoryInterface
	{
		return $this->streamFactory ??= $this->getMultiFactory();
	}


	protected function createAdisContentProvider(TransportProvider $transportProvider): Adis\ContentProvider
	{
		return new Adis\ContentProvider(new Adis\Client($transportProvider), new StatusBusinessSubjectsTransformer());
	}


	public function createTransportProvider(StreamFactoryInterface $streamFactory): TransportProvider
	{
		$client = $this->getClient();
		$requestFactory = $this->getRequestFactory();
		return new TransportProvider($requestFactory, $client, $streamFactory);
	}


	/**
	 * @return multiFactory
	 */
	protected function getMultiFactory(): RequestFactoryInterface|StreamFactoryInterface
	{
		self::checkGuzzle();

		return $this->multiFactory ??= class_exists(GuzzleHttp\Psr7\HttpFactory::class) ? new GuzzleHttp\Psr7\HttpFactory() : new HttpFactory();
	}


	private static function checkGuzzle(): void
	{
		if (!class_exists(GuzzleHttp\Client::class)) {
			throw new InvalidStateException('Guzzle not found, let implement own solution or install guzzle by: composer require guzzlehttp/guzzle');
		}
	}

}
