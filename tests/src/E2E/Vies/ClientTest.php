<?php

declare(strict_types=1);

namespace h4kuna\Ares\Tests\E2E\Vies;

require_once __DIR__ . '/../../../bootstrap.php';

use Closure;
use h4kuna\Ares;
use Tester\Assert;
use Tester\Environment;
use Tester\TestCase;
use Throwable;

final class ClientTest extends TestCase
{
	/**
	 * @return array<string|int, array{0: Closure(static):void}>
	 */
	public static function dataCheckVat(): array
	{
		return [
			[
				function (self $self) {
					$self->assertCheckVat('ATU73528319', true);
				},
			],
			[
				function (self $self) {
					$self->assertCheckVat('CZ27082440', true);
				},
			],
			[
				function (self $self) {
					$self->assertCheckVat('CZ-27082440', new Ares\Exception\ServerResponseException('VOW-ERR-2600: The VAT number does not match the following regular expression : "{0}".'));
				},
			],
			[
				function (self $self) {
					$self->assertCheckVat(new Ares\Vies\ViesEntity('27082440', 'CZ'), true);
				},
			],
			[
				function (self $self) {
					$self->assertCheckVat('CZ12345678', false);
				},
			],
		];
	}


	/**
	 * @param Closure(static):void $assert
	 * @dataProvider dataCheckVat
	 */
	public function testCheckVat(Closure $assert): void
	{
		$assert($this);
	}


	public function assertCheckVat(string|Ares\Vies\ViesEntity $vatNumber, bool|Throwable $expected): void
	{
		$aresFactory = (new Ares\AresFactory())->create();

		if ($expected instanceof Throwable) {
			Assert::throws(function () use ($aresFactory, $vatNumber): void {
				$aresFactory->checkVatVies($vatNumber);
			}, $expected::class, $expected->getMessage());
			return;
		}

		try {
			$response = $aresFactory->checkVatVies($vatNumber);
		} catch (Ares\Exception\ServerResponseException $e) {
			Assert::true(in_array($e->getMessage(), ['MS_UNAVAILABLE', 'MS_MAX_CONCURRENT_REQ'], true));
			Environment::skip('VIES service is unavailable');
			return;
		}
		Assert::same($expected, $response->valid);
	}


	public function testStatus(): void
	{
		$aresFactory = (new Ares\AresFactory())->create();
		$status = $aresFactory->viesContentProvider->status();
		assert(isset($status->vow->available));
		Assert::true($status->vow->available);
	}
}

(new ClientTest())->run();
