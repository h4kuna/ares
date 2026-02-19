<?php declare(strict_types = 1);

namespace h4kuna\Ares\Tool;

use h4kuna\Ares\Exception\LogicException;
use function get_debug_type;
use function is_float;
use function is_int;
use function is_string;
use function sprintf;

final class Integer
{

	public static function fromMixed(mixed $value): ?int
	{
		if (is_int($value) || is_float($value) || is_string($value)) {
			return (int) $value;
		} elseif ($value === null) {
			return null;
		}

		throw new LogicException(sprintf('Expected int, float, string or null, got %s.', get_debug_type($value)));
	}

}
