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
	public function testDataBox(): void
	{
		$ares = (new AresFactory())->create();
		$data = $ares->loadDataBox('27082440');
		Assert::same('xtkcrkz', $data->ISDS);
	}

}

(new DataBoxTest)->run();
