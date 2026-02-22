<?php declare(strict_types = 1);

namespace h4kuna\Ares\Tool;

use Closure;
use function array_chunk;
use function array_keys;

final class Batch
{

	/**
	 * @param Closure(string): string $callback
	 * @param array<NAME, string|int> $list
	 * @return array<string, array<(int&NAME)|(NAME&string)>>
	 *
	 * @template NAME
	 */
	public static function checkDuplicities(
		array $list,
		Closure $callback,
	): array
	{
		$duplicity = [];
		foreach ($list as $name => $value) {
			$newValue = $callback((string) $value);
			$duplicity[$newValue][] = $name;
		}

		return $duplicity;
	}

	/**
	 * @param array<string, array<NAME>> $list
	 * @param int<1, max> $batch
	 * @return array<array<string>>
	 *
	 * @template NAME
	 */
	public static function chunk(
		array $list,
		int $batch,
	): array
	{
		return array_chunk(array_keys($list), $batch);
	}

}
