<?php

namespace h4kuna\Ares;

use Salamium\Testinium,
	Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

/**
 * @author Milan Matějček
 */
class AresTest extends \Tester\TestCase
{

	/** @var Ares */
	private $ares;

	protected function setUp()
	{
		$this->ares = new Ares;
	}

	public function testFreelancer()
	{
		$in = '87744473';
		/* @var $data Data */
		$data = (string) $this->ares->loadData($in);
		Assert::same(Testinium\File::load($in . '.json'), $data);
	}

	public function testMenchart()
	{
		$in = '27082440';
		/* @var $data Data */
		$data = (string) $this->ares->loadData($in);
		Assert::same(Testinium\File::load($in . '.json'), $data);
	}

}

(new AresTest)->run();
