<?php declare(strict_types = 1);

namespace h4kuna\Ares\Tests\E2E;

use h4kuna\Ares\Ares\Client;
use h4kuna\Ares\Ares\Sources;
use h4kuna\Ares\AresFactory;
use h4kuna\Ares\Tests\TestCase;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class VrEndpointTest extends TestCase
{

	public function testBasic(): void
	{
		$aresFactory = new AresFactory();
		$transportProvider = $aresFactory->createTransportProvider($aresFactory->getStreamFactory());

		$provider = new Client($transportProvider);

		$coreData = $provider->useEndpoint(Sources::CORE, '87744473');
		Assert::same('87744473', $coreData->ico);

		$data = $provider->useEndpoint(Sources::SERVICE_RES, '87744473');
		Assert::same('87744473', $data->icoId);
	}

}

(new VrEndpointTest())->run();
