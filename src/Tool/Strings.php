<?php declare(strict_types=1);

namespace h4kuna\Ares\Tool;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

final class Strings
{

	public static function trimNull(?string $v): ?string
	{
		if ($v === null) {
			return null;
		}

		$string = trim($v);
		if ($string === '') {
			return null;
		}

		return $string;
	}


	public static function replaceSpace(string $string): string
	{
		return str_replace(' ', '', $string);
	}


	/**
	 * @return ($date is null ? null : DateTimeImmutable)
	 */
	public static function createDateTime(?string $date): ?DateTimeImmutable
	{
		if ($date === null) {
			return null;
		}
		return new DateTimeImmutable($date, new DateTimeZone('Europe/Prague'));
	}


	public static function exportDate(DateTimeInterface $date): string
	{
		return $date->format($date::RFC3339);
	}

}
