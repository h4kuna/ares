<?php declare(strict_types = 1);

namespace h4kuna\Ares\Tests\E2E;

use h4kuna\Ares\AresFactory;
use h4kuna\Ares\Tests\TestCase;
use h4kuna\Ares\Tests\UseStoredFile;
use function usort;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class DataBoxTest extends TestCase
{

	use UseStoredFile;

	protected static function getMask(): string
	{
		return __DIR__ . '/../../fixtures/databox/%file%.json';
	}

	/**
	 * @dataProvider provideBasic
	 */
	public function testBasic(string $in): void
	{
		$ares = (new AresFactory())->create();
		$data = $ares->loadDataBox($in);
		usort($data, static function ($a, $b) {
			return $a->ISDS <=> $b->ISDS;
		});
		$this->assertFile($in, $data);
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

(new DataBoxTest())->run();
