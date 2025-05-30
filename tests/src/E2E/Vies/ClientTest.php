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
					$self->assertCheckVat('CZ-27082440', new Ares\Exceptions\ServerResponseException('VOW-ERR-1: An unexpected error occurred. Please retry later or contact the support team.'));
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
		} catch (Ares\Exceptions\ServerResponseException $e) {
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
