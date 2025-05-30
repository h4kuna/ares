<?php declare(strict_types=1);

namespace h4kuna\Ares\Tool;

use stdClass;

final class Arrays
{
	/**
	 * @param stdClass|list<stdClass> $content
	 * @return list<stdClass>
	 */
	public static function fromStdClass(stdClass|array $content): array
	{
		if ($content instanceof stdClass) {
			return [$content];
		}

		/** @var list<stdClass> $content */
		return $content;
	}
}
