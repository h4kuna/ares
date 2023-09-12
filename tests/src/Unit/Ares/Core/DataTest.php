<?php declare(strict_types=1);

namespace h4kuna\Ares\Tests\Unit\Ares\Core;

use DateTimeImmutable;
use h4kuna\Ares\Ares\Core\Data;
use h4kuna\Ares\Ares\Sources;
use h4kuna\Ares\Tests\TestCase;
use Tester\Assert;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * @testCase
 */
final class DataTest extends TestCase
{

	public function testSerialize(): void
	{
		$data = self::createData();

		$unserializeData = unserialize(serialize($data));
		$data->original = null;
		Assert::equal($unserializeData, $data);
	}


	public function testToArray(): void
	{
		$data = self::createData();
		$arrayData = $data->toArray();
		$properties = self::allPropertyRead();
		foreach ($properties as $key => $name) {
			$data->{$name}; // @phpstan-ignore-line touch
			Assert::true(array_key_exists($name, $arrayData), $name);
			unset($properties[$key]);
		}
		Assert::same([], $properties);
	}


	public function testJson(): void
	{
		$data = self::createData();
		$encode = json_encode($data);
		Assert::same((string) $data, $encode);
		Assert::same('{"active":true,"city":"a","company":"b","created":"2020-12-13T04:05:06+01:00","dissolved":"2021-01-14T05:06:07+01:00","city_district":"c","city_post":"d","in":"e","is_person":false,"legal_form_code":102,"house_number":"f","street":"g","tin":null,"vat_payer":false,"zip":"h","country":"j","country_code":"k","nace":["465"],"sources":{"stavZdrojeRes":true}}', $encode);
	}


	/**
	 * @return array<string>
	 */
	private static function allPropertyRead(): array
	{
		$reflection = new \ReflectionClass(Data::class);
		$properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
		$properties = array_column($properties, 'name');
		foreach (['original', 'adis'] as $property) {
			$key = array_search($property, $properties, true);
			unset($properties[$key]);
		}

		return $properties;
	}


	private static function createData(): Data
	{
		$data = new Data();
		$data->active = true;
		$data->city = 'a';
		$data->company = 'b';
		$data->created = new DateTimeImmutable('2020-12-13 04:05:06');
		$data->dissolved = new DateTimeImmutable('2021-01-14 05:06:07');
		$data->city_district = 'c';
		$data->city_post = 'd';
		$data->in = 'e';
		$data->is_person = false;
		$data->legal_form_code = 102;
		$data->house_number = 'f';
		$data->street = 'g';
		$data->tin = null;
		$data->vat_payer = false;
		$data->zip = 'h';
		$data->nace = ['465'];
		$data->sources = [Sources::SERVICE_RES => true];
		$data->country = 'j';
		$data->country_code = 'k';
		$data->original = new \stdClass();

		return $data;
	}

}

(new DataTest())->run();
