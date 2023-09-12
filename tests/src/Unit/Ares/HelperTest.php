<?php declare(strict_types=1);

namespace h4kuna\Ares\Tests\Unit\Ares;

use h4kuna\Ares\Ares\Helper;
use h4kuna\Ares\Tests\TestCase;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class HelperTest extends TestCase
{
	/**
	 * @return array<array<mixed>>
	 */
	protected function provideParseAddress(): array
	{
		return [
			[
				'Mělnická 31, 25002 Brandýs n/L. - Stará Boleslav',
				[
					'zip' => '25002',
					'city' => 'Brandýs n/L. - Stará Boleslav',
					'street' => 'Mělnická',
					'house_number' => '31',
				],
			],
			[
				'Ruprechtická 319/16a, Liberec I-Staré Město, 46001 Liberec',
				[
					'zip' => '46001',
					'city' => 'Liberec',
					'street' => 'Ruprechtická',
					'house_number' => '319/16a',
				],
			],
			[
				'Jankovcova 1522/53, Holešovice, 17000 Praha 1',
				[
					'zip' => '17000',
					'city' => 'Praha 1',
					'street' => 'Jankovcova',
					'house_number' => '1522/53',
				],
			],
			[
				'Bělohorská 2428/203, Břevnov, 16900 Praha 6',
				[
					'zip' => '16900',
					'city' => 'Praha 6',
					'street' => 'Bělohorská',
					'house_number' => '2428/203',
				],
			],
			[
				'Budovcova 105/4, Budějovické Předměstí, 39701 Písek',
				[
					'zip' => '39701',
					'city' => 'Písek',
					'street' => 'Budovcova',
					'house_number' => '105/4',
				],
			],
			[
				'Brno, 60300, Staré Brno, Křídlovická, 24a',
				[
					'zip' => '60300',
					'city' => 'Brno',
					'street' => 'Křídlovická',
					'house_number' => '24a',
				],
			],
		];
	}


	/**
	 * @dataProvider provideParseAddress
	 * @param array<string, string> $expected
	 */
	public function testParseAddress(string $address, array $expected): void
	{
		Assert::equal($expected, Helper::parseAddress($address));
	}

}

(new HelperTest())->run();
