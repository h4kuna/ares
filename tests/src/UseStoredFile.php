<?php declare(strict_types=1);

namespace h4kuna\Ares\Tests;

use Nette\Utils\Json;
use Tester\Assert;

trait UseStoredFile
{

	protected function assertFile(string $filename, mixed $content): void
	{
		$filename = strtr(static::getMask(), ['%file%' => $filename]);
		$extension = pathinfo($filename, PATHINFO_EXTENSION);
		if (is_file($filename) === false) {
			file_put_contents($filename, self::saveContent($content, $extension));
		}

		Assert::same(file_get_contents($filename), self::saveContent($content, $extension));
	}


	protected static function getMask(): string
	{
		return '%file%';
	}


	private static function saveContent(mixed $content, string $extension): string
	{
		$jsonReflection = new \ReflectionClass(Json::class);
		$isOld = $jsonReflection->getMethod('encode')->getParameters()[1]->name === 'options';

		if (is_string($content)) {
			if ($extension === 'json') {
				return self::jsonToString(Json::decode($content), $isOld);
			}
			return $content;
		}

		return match ($extension) {
			'json' => self::jsonToString($content, $isOld),
			default => throw new \Exception('not implemented'),
		};
	}


	private static function jsonToString(mixed $content, bool $isOld): string
	{
		return $isOld ? strtr(Json::encode($content, Json::PRETTY), ['\\/' => '/']) : Json::encode($content, true);
	}
}
