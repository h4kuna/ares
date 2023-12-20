<?php declare(strict_types=1);

namespace h4kuna\Ares\Tests\E2E;

use h4kuna\Ares\AresFactory;
use h4kuna\Ares\Tests\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class DataBoxTest extends TestCase
{

	/**
	 * @dataProvider provideBasic
	 */
	public function testBasic(string $in): void
	{
		$ares = (new AresFactory())->create();
		$data = $ares->loadDataBox($in);
		$file = __DIR__ . "/../E2E/DataBox/$in.ser";
		$expected = unserialize(trim((string) file_get_contents($file)));
		assert(is_array($expected));
		foreach ($expected as $k => $v) {
			// file_put_contents($file, serialize($data));
			Assert::equal($v, $data[$k]);
		}
	}


	/**
	 * @return array<mixed>
	 */
	protected function provideBasic(): array
	{
		return [
			['00007064'],
			['27082440'],
		];
	}

}

(new DataBoxTest)->run();
