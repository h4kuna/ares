<?php declare(strict_types = 1);

namespace h4kuna\Ares\Tests\E2E\Ares;

use h4kuna\Ares\AresFactory;
use h4kuna\Ares\Exception\IdentificationNumberNotFoundException;
use h4kuna\Ares\Tests\TestCase;
use h4kuna\Ares\Tests\UseStoredFile;
use Tester\Assert;
use Throwable;
use function sort;
use const SORT_NUMERIC;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class CoreTest extends TestCase
{

	use UseStoredFile;

	protected static function getMask(): string
	{
		return __DIR__ . '/../../../fixtures/ares/%file%.json';
	}

	/**
	 * @return array<array<string>>
	 */
	protected function provideCore(): array
	{
		return [
			['26005492'], // read address from sidlo
			['26577321'], // address from dalsiUdaje[0]->sidlo[0]->sidlo
			['25528351'], // diff address
			['67909442'], // create date does not exist
			['27735753'], // create date does not exist
			['61682039'],
			['08975884'], // address
			['2445344'], // Skanska Residential a.s., DIČ CZ699004845
			['2491427'], // o.s.
			['5560438'],
			['16415345'],
			['25110161'], // v.o.s.
			['26713250'],
			['27082440'], // a.s.
			['49240901'], // Raiffeisenbank a.s., DIČ CZ699003154
			['49812670'], // s.r.o.
			['62413686'], // k.s.
			['87744473'], // freelancer
			['00841811'], // Němčice
			['17686091'], // Svěřenecký fond
		];
	}

	/**
	 * @dataProvider provideCore
	 */
	public function testCore(string $in): void
	{
		$data = (new AresFactory())->create()->loadBasic($in);
		sort($data->nace, SORT_NUMERIC);
		$this->assertFile($data->in, $data);
	}

	// phpcs:disable SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly.ReferenceViaFullyQualifiedName
	/**
	 * @throws \h4kuna\Ares\Exception\IdentificationNumberNotFoundException
	 */
	// phpcs:enable SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly.ReferenceViaFullyQualifiedName
	public function testInactive(): void
	{
		$data = (new AresFactory())->create()->loadBasic('25596641');
		Assert::false($data->active);
	}

	public function testGroupVAT(): void
	{
		$in = '2445344';
		$data = (new AresFactory())->create()->loadBasic($in);
		Assert::same('CZ699004845', $data->tin);
		Assert::true($data->vat_payer);
	}

	public function testForeignPerson(): void
	{
		$data = (new AresFactory())->create()->loadBasic('6387446');
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
		$results = (new AresFactory())->create()->loadBasicMulti($identificationNumbers);
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
		$results = (new AresFactory())->create()->loadBasicMulti([]);
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
			(new AresFactory())->create()->loadBasic($in);
			Assert::fail('Must throw exception');
		} catch (IdentificationNumberNotFoundException $e) {
			Assert::same($in, $e->getIn());
		} catch (Throwable $e) {
			Assert::fail('Must throw ' . IdentificationNumberNotFoundException::class);
		}
	}

}

(new CoreTest())->run();
