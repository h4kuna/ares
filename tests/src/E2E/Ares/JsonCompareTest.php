<?php declare(strict_types=1);

namespace h4kuna\Ares\Tests\E2E\Ares;

use h4kuna\Ares\Tests\TestCase;
use Nette\Utils\Json;
use Tester\Assert;
use Tester\Environment;

require __DIR__ . '/../../../bootstrap.php';

final class JsonCompareTest extends TestCase
{
	public function testCompareJson(): void
	{
		if (getenv('GITHUB_RUN') === '1') {
			Environment::skip('Forbidden for github.');
		}

		$remote = Json::decode((string) file_get_contents('https://ares.gov.cz/ekonomicke-subjekty-v-be/rest/v3/api-docs'));
		$local = Json::decode((string) file_get_contents(__DIR__ . '/../../../AresRestApi-verejne.json'));
		Assert::equal($local, $remote);
	}
}

(new JsonCompareTest())->run();
