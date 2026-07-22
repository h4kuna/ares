<?php declare(strict_types = 1);

namespace h4kuna\Ares\Tests\Unit\Adis\Soap;

use h4kuna\Ares\Adis\Soap\Envelope;
use h4kuna\Ares\Tests\TestCase;
use Tester\Assert;
use function libxml_use_internal_errors;
use function simplexml_load_string;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * @testCase
 */
final class EnvelopeTest extends TestCase
{

	/**
	 * @return array<array<string>>
	 */
	protected function provideEnvelopes(): array
	{
		return [
			[Envelope::seznamNespolehlivyPlatce()],
			[Envelope::statusNespolehlivyPlatce('CZ12345678')],
			[Envelope::statusNespolehlivyPlatceRozsireny('CZ12345678', 'CZ87654321')],
			[Envelope::statusNespolehlivySubjektRozsireny('CZ12345678', 'CZ87654321')],
		];
	}

	/**
	 * @dataProvider provideEnvelopes
	 */
	public function testEnvelopeIsValidXml(string $xml): void
	{
		libxml_use_internal_errors(true);
		Assert::notEqual(false, simplexml_load_string($xml));
	}

}

(new EnvelopeTest())->run();
