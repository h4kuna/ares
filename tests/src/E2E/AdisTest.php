<?php declare(strict_types=1);

namespace h4kuna\Ares\Tests\E2E;

use h4kuna;
use h4kuna\Ares;
use h4kuna\Ares\Tests\TestCase;
use Nette\Utils\Json;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

final class AdisTest extends TestCase
{
	/**
	 * @return array<mixed>
	 */
	protected function provideOneTin(): array
	{
		return [
			['8702080024'],
			['CZ8612301071'],
			['CZ06303927'],
			['CZ27082440'],
			['CZ49240901'],
			['CZ44932553'],
		];
	}


	/**
	 * @dataProvider provideOneTin
	 */
	public function testOneTin(string $tin): void
	{
		$adis = (new Ares\AresFactory())->create()->adisContentProvider;

		$subject = $adis->statusBusinessSubject($tin);
		Assert::type(Ares\Adis\StatusBusinessSubjects\Subject::class, $subject);
		Assert::equal(loadResult("adis/$subject->tin"), Json::decode(Json::encode($subject)));
	}


	public function testMulti(): void
	{
		$adis = (new Ares\AresFactory())->create()->adisContentProvider;

		$tins = [
			'a' => '8702080024',
			'b' => 'CZ8612301071',
			'c' => 'CZ8612301071',
			'd' => 'CZ06303927',
			'e' => 'CZ27082440',
			'f' => 'CZ49240901',
		];

		$results = [];
		foreach ($adis->statusBusinessSubjects($tins) as $name => $subject) {
			Assert::type(Ares\Adis\StatusBusinessSubjects\Subject::class, $subject);
			Assert::equal(loadResult("adis/$subject->tin"), Json::decode(Json::encode($subject)));
			$results[$name] = $subject;
		}

		Assert::same($results['b'], $results['c']);
	}

}

(new AdisTest())->run();
