<?php declare(strict_types=1);

namespace h4kuna\Ares\BusinessList;

use h4kuna\Ares\Exceptions\IdentificationNumberNotFoundException;
use h4kuna\Ares\Http\RequestProvider;
use Nette\Utils\Json;
use Nette\Utils\Strings;

final class ContentProvider
{
	private RequestProvider $requestProvider;


	public function __construct(RequestProvider $requestProvider)
	{
		$this->requestProvider = $requestProvider;
	}


	/**
	 * @throws IdentificationNumberNotFoundException
	 */
	public function load(string $in): \stdClass
	{
		$answer = $this->requestProvider->businessList($in);

		$data = Json::decode(Json::encode($answer));
		assert($data instanceof \stdClass);

		return self::fixContent($data);
	}


	private static function fixContent(\stdClass $data): \stdClass
	{
		self::walk($data->Vypis_OR, function ($value) {
			return Strings::replace(trim($value), '/ {2,}/', ' ');
		});

		return $data;
	}


	/**
	 * @param array<mixed>|object|null $value
	 */
	private static function walk(&$value, \Closure $callback): void
	{
		if ($value === null) {
			return;
		}

		array_walk($value, function (&$value) use ($callback) {
			if (is_string($value)) {
				$value = ($callback)($value);

				return;
			}

			self::walk($value, $callback);
		});
	}

}
