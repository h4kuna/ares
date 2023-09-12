<?php declare(strict_types=1);

namespace h4kuna\Ares\Tests\Unit\Ares\Core;

use h4kuna\Ares\Ares\Core\JsonToDataTransformer;
use h4kuna\Ares\Tests\TestCase;
use Tester\Assert;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * @testCase
 */
final class JsonToDataTransformerTest extends TestCase
{

	public function testCompare(): void
	{
		$json = new \stdClass();
		$json->ico = '1';
		$json->pravniForma = '111';
		$json->datumVzniku = '2022-08-13';

		$data = (new JsonToDataTransformer())->transform($json);

		$array = $data->toArray();
		if ($array === []) {
			Assert::fail('Empty array');
		}

		foreach ($array as $name => $v) {
			$data->{$name}; // @phpstan-ignore-line touch
		}

		Assert::true(true);
	}

}

(new JsonToDataTransformerTest)->run();
