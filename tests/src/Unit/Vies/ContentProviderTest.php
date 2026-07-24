<?php declare(strict_types = 1);

namespace h4kuna\Ares\Tests\Unit\Vies;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\HttpFactory;
use h4kuna\Ares\Exception\LogicException;
use h4kuna\Ares\Http\TransportProvider;
use h4kuna\Ares\Tests\TestCase;
use h4kuna\Ares\Vies\Client;
use h4kuna\Ares\Vies\ContentProvider;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class ContentProviderTest extends TestCase
{

	/**
	 * @return array<array<string>>
	 */
	protected function provideInvalidVatNumbers(): array
	{
		return [
			['12345678'], // no country prefix
			['1CZ2345678'], // country code not at the beginning
			['cz12345678'], // lowercase prefix
		];
	}

	/**
	 * @dataProvider provideInvalidVatNumbers
	 */
	public function testCheckVatRejectsVatNumberWithoutCountryPrefix(string $vatNumber): void
	{
		$httpFactory = new HttpFactory();
		$contentProvider = new ContentProvider(new Client(new TransportProvider($httpFactory, new GuzzleClient(), $httpFactory)));

		Assert::exception(
			static fn () => $contentProvider->checkVat($vatNumber),
			LogicException::class,
		);
	}

}

(new ContentProviderTest())->run();
