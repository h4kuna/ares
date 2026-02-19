<?php declare(strict_types = 1);

namespace h4kuna\Ares;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\HttpFactory as GuzzleHttpFactory;
use h4kuna\Ares\Adis\Client as AdisClient;
use h4kuna\Ares\Adis\ContentProvider as AdisContentProvider;
use h4kuna\Ares\Adis\StatusBusinessSubjects\StatusBusinessSubjectsTransformer;
use h4kuna\Ares\Ares\Client as AresClient;
use h4kuna\Ares\Ares\Core\ContentProvider as AresCoreContentProvider;
use h4kuna\Ares\Ares\Core\JsonToDataTransformer;
use h4kuna\Ares\DataBox\Client as DataBoxClient;
use h4kuna\Ares\DataBox\ContentProvider as DataBoxContentProvider;
use h4kuna\Ares\Exception\LogicException;
use h4kuna\Ares\Http\HttpFactory;
use h4kuna\Ares\Http\TransportProvider;
use h4kuna\Ares\Vies\Client;
use h4kuna\Ares\Vies\ContentProvider;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use function class_exists;

/**
 * @phpstan-type multiFactory RequestFactoryInterface&StreamFactoryInterface
 */
class AresFactory
{

	/**
	 * @var multiFactory|null
	 */
	private RequestFactoryInterface|StreamFactoryInterface|null $multiFactory = null;


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
		$aresClient = new AresClient($transportProvider);

		$dataBoxClient = new DataBoxClient($transportProvider);
		$dataBoxContentProvider = new DataBoxContentProvider($dataBoxClient, $streamFactory);

		$aresContentProvider = new AresCoreContentProvider(new JsonToDataTransformer(), $aresClient, $adisContentProvider);

		$viesContentProvider = new ContentProvider(new Client($transportProvider));

		return new Ares($aresContentProvider, $dataBoxContentProvider, $adisContentProvider, $viesContentProvider);
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

		return $this->client = new GuzzleClient();
	}

	public function getStreamFactory(): StreamFactoryInterface
	{
		return $this->streamFactory ??= $this->getMultiFactory();
	}

	protected function createAdisContentProvider(TransportProvider $transportProvider): AdisContentProvider
	{
		return new AdisContentProvider(new AdisClient($transportProvider), new StatusBusinessSubjectsTransformer());
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

		return $this->multiFactory ??= class_exists(GuzzleHttpFactory::class) ? new GuzzleHttpFactory() : new HttpFactory();
	}

	private static function checkGuzzle(): void
	{
		if (class_exists(GuzzleClient::class) === false) {
			throw new LogicException('Guzzle not found, let implement own solution or install guzzle by: composer require guzzlehttp/guzzle');
		}
	}

}
