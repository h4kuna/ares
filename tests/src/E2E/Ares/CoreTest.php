<?php declare(strict_types=1);

namespace h4kuna\Ares\Tests\E2E\Ares;

use h4kuna;
use h4kuna\Ares;
use h4kuna\Ares\Tests\TestCase;
use Nette\Utils\Json;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class CoreTest extends TestCase
{
	/**
	 * @return array<array<string>>
	 */
	protected function provideCore(): array
	{
		return [
			['2445344'], // Skanska Residential a.s., DIČ CZ699004845
			['2491427'],  // o.s.
			['5560438'],
			['16415345'],
			['25110161'], // v.o.s.
			['25596641'], // inactive
			['26713250'],
			['27082440'], // a.s.
			['49240901'], // Raiffeisenbank a.s., DIČ CZ699003154
			['49812670'], // s.r.o.
			['61682039'],
			['62413686'], // k.s.
			['87744473'], // freelancer
		];
	}


	/**
	 * @dataProvider provideCore
	 */
	public function testCore(string $in): void
	{
		$data = (new Ares\AresFactory())->create()->loadBasic($in);
		$json = Json::decode(Json::encode($data));
		Assert::equal(loadResult("ares/$data->in"), $json);
	}


	public function testInactive(): void
	{
		$data = (new Ares\AresFactory())->create()->loadBasic('25596641');
		Assert::false($data->active);
	}


	public function testGroupVAT(): void
	{
		$in = '2319918';
		$data = (new Ares\AresFactory())->create()->loadBasic($in);
		Assert::null($data->tin);
		Assert::true($data->vat_payer);
	}


	public function testForeignPerson(): void
	{
		$data = (new Ares\AresFactory())->create()->loadBasic('6387446');
		Assert::true($data->is_person);
	}


	public function testLoadBasicMulti(): void
	{
		$identificationNumbers = [
			'one' => '6387446',
			'two' => '123',
			'three' => '87744473',
			'four' => '25596641',
			'five' => '06387446',
		];
		$results = (new Ares\AresFactory())->create()->loadBasicMulti($identificationNumbers);
		$companies = [];
		foreach ($results as $in => $result) {
			$companies[$in] = $result;
		}

		Assert::count(3, $companies);
		Assert::same($companies['one'], $companies['five']);
		Assert::true(isset($companies['three']));
	}


	public function testLoadBasicMultiEmpty(): void
	{
		$identificationNumbers = [];
		$results = (new Ares\AresFactory())->create()->loadBasicMulti([]);
		foreach ($results as $result) {
			$identificationNumbers[] = $result;
		}

		Assert::same([], $identificationNumbers);
	}


	/**
	 * @return array<array<string>>
	 */
	protected function provideNotFound(): array
	{
		return [
			['36620751'], // canceled
			['123'], // never exists
		];
	}


	/**
	 * @dataProvider provideNotFound
	 */
	public function testNotFound(string $in): void
	{
		try {
			(new Ares\AresFactory())->create()->loadBasic($in);
			Assert::fail('Must throw exception');
		} catch (Ares\Exceptions\IdentificationNumberNotFoundException $e) {
			Assert::same($in, $e->getIn());
		} catch (\Throwable $e) {
			Assert::fail('Must throw ' . Ares\Exceptions\IdentificationNumberNotFoundException::class);
		}
	}

}

(new CoreTest)->run();
