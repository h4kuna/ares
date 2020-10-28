<?php declare(strict_types=1);

namespace h4kuna\Ares\Tests;

use Salamium\Testinium;
use Tester\Assert;
use h4kuna\Ares;

require_once __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
class AresTest extends TestCase
{

	/**
	 * @throws \h4kuna\Ares\Exceptions\IdentificationNumberNotFoundException
	 */
	public function testNotExists(): void
	{
		(new Ares\Ares)->loadData('36620751');
	}


	public function testFreelancer(): void
	{
		$ares = new Ares\Ares;
		$in = '87744473';
		/* @var $data Ares\Data */
		$data = (string) $ares->loadData($in);
		// Testinium\File::save($in . '.json', (string) $data);
		Assert::same(Testinium\File::load($in . '.json'), $data);
	}


	public function testMerchant(): void
	{
		$ares = new Ares\Ares;
		$in = '27082440';
		/* @var $data Ares\Data */
		$data = (string) $ares->loadData($in);
		// Testinium\File::save($in . '.json', (string) $data);
		Assert::same(Testinium\File::load($in . '.json'), $data);
	}


	public function testMerchantInActive(): void
	{
		$ares = new Ares\Ares;
		$in = '25596641';
		/* @var $data Ares\Data */
		$data = json_encode($ares->loadData($in));
		// Testinium\File::save($in . '.json', (string) $data);
		Assert::same(Testinium\File::load($in . '.json'), $data);
	}


	public function testHouseNumber(): void
	{
		$ares = new Ares\Ares;
		$in = '26713250';
		/* @var $data Ares\Data */
		$data = json_encode($ares->loadData($in));
		// Testinium\File::save($in . '.json', (string) $data);
		Assert::same(Testinium\File::load($in . '.json'), $data);
	}


	public function testToArray(): void
	{
		$ares = new Ares\Ares;
		$data = $ares->loadData('87744473');
		Assert::same('Milan Matějček', $data->company);

		$names = [];
		foreach (self::allPropertyRead($data) as $value) {
			if (!preg_match('~\$(?P<name>.*)~', $value, $find)) {
				throw new \RuntimeException('Bad annotation property-read od Data class: ' . $value);
			}
			Assert::true($data->exists($find['name']));
			$names[$find['name']] = true;
		}

		Assert::same([], array_diff_key($data->getData(), $names));

		Assert::same([
			"620",
			"461",
			"471",
			"73110",
			"7490",
		], $data->nace);

		Assert::type('array', $data->toArray());
		Assert::same([
			'c' => 'Milan Matějček',
			'company' => true,
			'city' => 'Mladá Boleslav',
		],
			$data->toArray(['company' => 'c', 'is_person' => 'company', 'city' => null]));
	}


	/**
	 * @throws \h4kuna\Ares\Exceptions\IdentificationNumberNotFoundException
	 */
	public function testNoIn(): void
	{
		(new Ares\Ares)->loadData('123');
	}


	public function testForeingPerson(): void
	{
		$data = (new Ares\Ares)->loadData('6387446');
		Assert::true($data->is_person);
	}


	/**
	 * @return array<string>
	 */
	private static function allPropertyRead(Ares\Data $data): array
	{
		$doc = (new \ReflectionClass($data))->getDocComment();
		if ($doc === false) {
			throw new \RuntimeException();
		}

		preg_match_all('/@property-read *(?P<propertyRead>.+)/', $doc, $match);
		return $match['propertyRead'];
	}


	public function testLoadByIdentificationNumbers()
	{
		$identificationNumbers = ['6387446', '123', '87744473', '25596641'];
		$results = (new Ares\Ares)->loadByIdentificationNumbers($identificationNumbers);
		Assert::count(4, $results);
		Assert::same([
			'c' => 'Ivan Šebesta',
			'company' => true,
			'city' => 'Břest',
		], $results[0]->toArray(['company' => 'c', 'is_person' => 'company', 'city' => null]));

		Assert::same([
			'code' => 0,
			'message' => 'Chyba 71 - nenalezeno 123',
		], $results[1]->toArray());
		Assert::same([
			'c' => 'Milan Matějček',
			'company' => true,
			'city' => 'Mladá Boleslav',
		], $results[2]->toArray(['company' => 'c', 'is_person' => 'company', 'city' => null]));
		Assert::same([
			'code' => 0,
			'message' => 'Chyba 61 - subjekt zanikl'
		], $results[3]->toArray(['company' => 'c', 'is_person' => 'company', 'city' => null]));
	}

}

(new AresTest)->run();
