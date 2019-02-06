<?php

namespace h4kuna\Ares;

use Salamium\Testinium;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

class AresTest extends \Tester\TestCase
{

	public function testFreelancer()
	{
		$ares = new Ares;
		$in = '87744473';
		/* @var $data Data */
		$data = (string) $ares->loadData($in);
		// Testinium\File::save($in . '.json', (string) $data);
		Assert::same(Testinium\File::load($in . '.json'), $data);
	}


	public function testMenchart()
	{
		$ares = new Ares;
		$in = '27082440';
		/* @var $data Data */
		$data = (string) $ares->loadData($in);
		// Testinium\File::save($in . '.json', (string) $data);
		Assert::same(Testinium\File::load($in . '.json'), $data);
	}


	public function testMenchartInActive()
	{
		$ares = new Ares;
		$in = '25596641';
		/* @var $data Data */
		$data = json_encode($ares->loadData($in));
		// Testinium\File::save($in . '.json', (string) $data);
		Assert::same(Testinium\File::load($in . '.json'), $data);
	}


	public function testToArray()
	{
		$ares = new Ares;
		$data = $ares->loadData('87744473');
		Assert::same('Milan Matějček', $data->company);

		$names = [];
		$propertyRead = \Nette\Reflection\AnnotationsParser::getAll(new \ReflectionClass($data))['property-read'];
		foreach ($propertyRead as $value) {
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
	public function testNoIn()
	{
		(new Ares)->loadData('123');
	}

}

(new AresTest)->run();
