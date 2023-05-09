<?php declare(strict_types=1);

namespace h4kuna\Ares\Tests;

use h4kuna\Ares;
use h4kuna\Ares\Exceptions\IdentificationNumberNotFoundException;
use Nette\Utils\Json;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
class AresTest extends TestCase
{
	public function testDataBox(): void
	{
		$ares = (new Ares\AresFactory())->create();
		$data = $ares->loadDataBox('27082440');
		Assert::same('xtkcrkz', $data->ISDS);
	}


	/**
	 * @dataProvider BusinessListData
	 */
	public function testBusinessList(string $in): void
	{
		$ares = (new Ares\AresFactory())->create();
		try {
			$data = $ares->loadBusinessList($in);
		} catch (IdentificationNumberNotFoundException $e) {
			Assert::same('87744473', $e->getIn());

			return; // intentionally
		}

		$data->UVOD = new \stdClass();
		$data->ZAU->POD = '';
		$jsonData = Json::decode(Json::encode($data));
		Assert::equal(loadResult("bl-$in"), $jsonData);
	}


	/**
	 * @return array<array{in: string}>
	 */
	public static function BusinessListData(): array
	{
		return [
			[
				'in' => '27082440', // a.s.
			],
			[
				'in' => '49812670', // s.r.o.
			],
			[
				'in' => '87744473', // freelancer
			],
			[
				'in' => '25110161', // v.o.s.
			],
			[
				'in' => '62413686', // k.s.
			],
			[
				'in' => '02491427', // o.s.
			],
		];
	}


	/**
	 * @throws \h4kuna\Ares\Exceptions\IdentificationNumberNotFoundException
	 */
	public function testNotExists(): void
	{
		(new Ares\AresFactory())->create()->loadBasic('36620751');
	}


	public function testFreelancer(): void
	{
		$ares = (new Ares\AresFactory())->create();
		$in = '87744473';
		$aresData = $ares->loadBasic($in);
		$data = json_decode((string) $aresData);
		Assert::equal(loadResult($in), $data);

		Assert::same('N', $aresData->psu(Ares\Basic\SubjectFlag::VR_2));
		Assert::same('A', $aresData->psu(Ares\Basic\SubjectFlag::RES_3));
		Assert::same('A', $aresData->psu(Ares\Basic\SubjectFlag::RZP_4));
		Assert::same('N', $aresData->psu(Ares\Basic\SubjectFlag::NRPZS_5));
		Assert::same('A', $aresData->psu(Ares\Basic\SubjectFlag::RPDPH_6));

		Assert::equal($aresData, unserialize(serialize($aresData)));
	}


	public function testMerchant(): void
	{
		$ares = (new Ares\AresFactory())->create();
		$in = '27082440';
		$data = json_decode((string) $ares->loadBasic($in));
		Assert::equal(loadResult($in), $data);
	}


	public function testMerchantInActive(): void
	{
		$ares = (new Ares\AresFactory())->create();
		$in = '25596641';
		$data = json_decode((string) $ares->loadBasic($in));
		Assert::equal(loadResult($in), $data);
	}


	public function testHouseNumber(): void
	{
		$ares = (new Ares\AresFactory())->create();
		$in = '26713250';
		$data = json_decode((string) $ares->loadBasic($in));
		Assert::equal(loadResult($in), $data);
	}


	public function testGroupVAT(): void
	{
		$ares = (new Ares\AresFactory())->create();
		$in = '02319918';
		$data = $ares->loadBasic($in);
		Assert::true($data->isGroupVat());
		Assert::null($data->tin);
        Assert::true($data->vat_payer);
	}


	public function testToArray(): void
	{
		$ares = (new Ares\AresFactory())->create();
		$data = $ares->loadBasic('87744473');
		Assert::same('Milan Matějček', $data->company);

		$names = [];
		$arrayData = $data->toArray();
		$properties = self::allPropertyRead();
		foreach ($properties as $key => $name) {
			Assert::true(array_key_exists($name, $arrayData));
			unset($properties[$key]);
		}

		Assert::same([], $properties);

		Assert::same(['461', '471', '620', '73110', '7490'], $data->nace);

		Assert::type('array', $data->toArray());
		Assert::same([
			'c' => 'Milan Matějček',
			'company' => true,
			'city' => 'Dolní Bousov',
		],
			$data->toArray(['company' => 'c', 'is_person' => 'company', 'city' => null]));
	}


	/**
	 * @throws \h4kuna\Ares\Exceptions\IdentificationNumberNotFoundException
	 */
	public function testNoIn(): void
	{
		(new Ares\AresFactory())->create()->loadBasic('123');
	}


	public function testForeingPerson(): void
	{
		$data = (new Ares\AresFactory())->create()->loadBasic('6387446');
		Assert::true($data->is_person);
	}


	/**
	 * @return array<string>
	 */
	private static function allPropertyRead(): array
	{
		$reflection = new \ReflectionClass(Ares\Basic\Data::class);
		$properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);

		return array_column($properties, 'name');
	}


	public function testLoadBasicMulti(): void
	{
		$identificationNumbers = ['6387446', '123', '87744473', '25596641'];
		$results = (new Ares\AresFactory())->create()->loadBasicMulti($identificationNumbers);
		Assert::count(2, $results[Ares\Ares::RESULT_FAILED]);
		Assert::count(2, $results[Ares\Ares::RESULT_SUCCESS]);
		Assert::same([
			'c' => 'Ivan Šebesta',
			'company' => true,
			'city' => 'Břest',
		], $results[Ares\Ares::RESULT_SUCCESS][0]->toArray([
			'company' => 'c',
			'is_person' => 'company',
			'city' => null,
		]));
		Assert::same([
			'c' => 'Milan Matějček',
			'company' => true,
			'city' => 'Dolní Bousov',
		], $results[Ares\Ares::RESULT_SUCCESS][2]->toArray([
			'company' => 'c',
			'is_person' => 'company',
			'city' => null,
		]));

		Assert::same([
			'in' => '123',
			'code' => 71,
			'message' => 'Chyba 71 - nenalezeno 123',
		], $results[Ares\Ares::RESULT_FAILED][1]->toArray());
		Assert::same([
			'in' => '25596641',
			'code' => 61,
			'message' => 'Chyba 61 - subjekt zanikl',
		], $results[Ares\Ares::RESULT_FAILED][3]->toArray());

		Assert::same(61, $results[Ares\Ares::RESULT_FAILED][3]->code);
		Assert::true($results[Ares\Ares::RESULT_FAILED][3]->disappeared());
		Assert::same('25596641', $results[Ares\Ares::RESULT_FAILED][3]->in);
		Assert::same('Chyba 61 - subjekt zanikl', $results[Ares\Ares::RESULT_FAILED][3]->message);
	}


    public function testBackCompatibilityClass(): void
    {
        Assert::type(Ares\Basic\Data::class, new Ares\Data\Data());
        Assert::same(Ares\Basic\SubjectFlag::VR_2, Ares\Data\SubjectFlag::VR_2);
    }

}

(new AresTest)->run();
