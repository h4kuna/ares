<?php declare(strict_types=1);

namespace h4kuna\Ares\Tests\E2E\Ares;

use h4kuna\Ares\Tests\TestCase;
use h4kuna\Ares\Tests\UseStoredFile;
use Tester\Environment;

require __DIR__ . '/../../../bootstrap.php';

final class JsonCompareTest extends TestCase
{
	use UseStoredFile;

	protected static function getMask(): string
	{
		return __DIR__ . '/../../../fixtures/%file%.json';
	}


	public function testCompareJson(): void
	{
		if (getenv('GITHUB_RUN') === '1') {
			Environment::skip('Forbidden for github.');
		}

		$this->assertFile('AresRestApi-verejne', (string) file_get_contents('https://ares.gov.cz/ekonomicke-subjekty-v-be/rest/v3/api-docs'));
	}
}

(new JsonCompareTest())->run();
