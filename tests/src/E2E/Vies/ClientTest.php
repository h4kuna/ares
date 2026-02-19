<?php declare(strict_types = 1);

namespace h4kuna\Ares\Tests\E2E\Vies;

require_once __DIR__ . '/../../../bootstrap.php';

use Closure;
use h4kuna\Ares\AresFactory;
use h4kuna\Ares\Exception\ServerResponseException;
use h4kuna\Ares\Vies\ViesEntity;
use Tester\Assert;
use Tester\Environment;
use Tester\TestCase;
use Throwable;
use function assert;
use function in_array;

final class ClientTest extends TestCase
{

	/**
	 * @return array<string|int, array{0: Closure(static):void}>
	 */
	public static function dataCheckVat(): array
	{
		return [
			[
				static function (self $self): void {
					$self->assertCheckVat('ATU73528319', true);
				},
			],
			[
				static function (self $self): void {
					$self->assertCheckVat('CZ27082440', true);
				},
			],
			[
				static function (self $self): void {
					$self->assertCheckVat('CZ-27082440', new ServerResponseException('VOW-ERR-2600: The VAT number does not match the following regular expression : "{0}".'));
				},
			],
			[
				static function (self $self): void {
					$self->assertCheckVat(new ViesEntity('27082440', 'CZ'), true);
				},
			],
			[
				static function (self $self): void {
					$self->assertCheckVat('CZ12345678', false);
				},
			],
		];
	}

	/**
	 * @param Closure(static):void $assert
	 *
	 * @dataProvider dataCheckVat
	 */
	public function testCheckVat(Closure $assert): void
	{
		$assert($this);
	}

	public function assertCheckVat(
		string|ViesEntity $vatNumber,
		bool|Throwable $expected,
	): void
	{
		$aresFactory = (new AresFactory())->create();

		if ($expected instanceof Throwable) {
			Assert::throws(static function () use ($aresFactory, $vatNumber): void {
				$aresFactory->checkVatVies($vatNumber);
			}, $expected::class, $expected->getMessage());
			return;
		}

		try {
			$response = $aresFactory->checkVatVies($vatNumber);
		} catch (ServerResponseException $e) {
			Assert::true(in_array($e->getMessage(), ['MS_UNAVAILABLE', 'MS_MAX_CONCURRENT_REQ'], true));
			Environment::skip('VIES service is unavailable');
			return;
		}
		Assert::same($expected, $response->valid);
	}

	public function testStatus(): void
	{
		$aresFactory = (new AresFactory())->create();
		$status = $aresFactory->viesContentProvider->status();
		assert(isset($status->vow->available));
		Assert::true($status->vow->available);
	}

}

(new ClientTest())->run();
