<?php declare(strict_types=1);

namespace h4kuna\Ares\Ares;

use h4kuna\Ares\Ares\Core\SubjectType;
use h4kuna\Ares\Exception\LogicException;
use h4kuna\Ares\Tool\Strings;
use Nette\Utils\Strings as NetteStrings;

/**
 * @phpstan-type addressTypeRaw array{street?: string, zip?: string, city?: string, house_number?: string, country?: string}
 * @phpstan-type addressType array{street: ?string, zip: ?string, city: ?string, house_number: ?string, country: ?string}
 */
final class Helper
{
	public static string $baseUrl = 'https://ares.gov.cz/ekonomicke-subjekty-v-be/rest';

	/**
	 * @var array<string, string>
	 */
	public static $endpoints = [
		Sources::SERVICE_VR => '/ekonomicke-subjekty-vr/{ico}',
		Sources::SERVICE_RES => '/ekonomicke-subjekty-res/{ico}',
		Sources::SERVICE_RZP => '/ekonomicke-subjekty-rzp/{ico}',
		Sources::SERVICE_NRPZS => '/ekonomicke-subjekty-nrpzs/{ico}',
		Sources::SERVICE_RCNS => '/ekonomicke-subjekty-rcns/{ico}',
		Sources::SERVICE_RPSH => '/ekonomicke-subjekty-rpsh/{ico}',
		Sources::SERVICE_RS => '/ekonomicke-subjekty-rs/{ico}',
		Sources::SERVICE_SZR => '/ekonomicke-subjekty-szr/{ico}',
		Sources::SERVICE_ROS => '/ekonomicke-subjekty-ros/{ico}',
		Sources::SERVICE_CEU => '/ekonomicke-subjekty-ceu/{ico}',
		Sources::CORE => '/ekonomicke-subjekty/{ico}',
		Sources::DIAL => '/ciselniky-nazevniky/{ico}',
	];

	private const SERVICES = [
		Sources::SERVICE_VR => 'NEEXISTUJICI',
		Sources::SERVICE_RES => 'NEEXISTUJICI',
		Sources::SERVICE_RZP => 'NEEXISTUJICI',
		Sources::SERVICE_NRPZS => 'NEEXISTUJICI',
		Sources::SERVICE_RCNS => 'NEEXISTUJICI',
		Sources::SERVICE_RPSH => 'NEEXISTUJICI',
		Sources::SERVICE_RS => 'NEEXISTUJICI',
		Sources::SERVICE_SZR => 'NEEXISTUJICI',
		Sources::SERVICE_CEU => 'NEEXISTUJICI',
		Sources::SER_NO_DPH => 'NEEXISTUJICI',
		Sources::SER_NO_IR => 'NEEXISTUJICI',
		Sources::SER_NO_RED => 'NEEXISTUJICI',
		Sources::SER_NO_SD => 'NEEXISTUJICI',
	];


	public static function endpointExists(string $source): bool
	{
		return isset(self::$endpoints[$source]);
	}


	/**
	 * @param Sources::SERVICE_*|Sources::DIAL|Sources::CORE $source
	 */
	public static function prepareUrlSearch(string $source): string
	{
		return self::prepareUrl($source, 'vyhledat');
	}


	/**
	 * @param Sources::SERVICE_*|Sources::DIAL|Sources::CORE $source
	 */
	public static function prepareUrl(string $source, string $in): string
	{
		if (self::endpointExists($source) === false) {
			throw new LogicException(sprintf('Endpoint %s does not exists.', $source));
		}

		return str_replace(
			'{ico}',
			self::normalizeIN($in),
			self::$baseUrl . self::$endpoints[$source],
		);
	}


	/**
	 * @param array<Sources::SER*, string> $registrations
	 * @return array<Sources::SER*, true|string>
	 */
	public static function services(array $registrations): array
	{
		$map = [];
		foreach ($registrations as $k => $v) {
			$map[$k] = $v === 'AKTIVNI' ? true : $v;
		}

		return $map + self::SERVICES;
	}


	public static function houseNumber(
		string $cisloDomovni,
		string $cisloOrientacni,
		string $cisloOrientacniPismeno
	): ?string
	{
		$houseNumber = Strings::trimNull(trim($cisloDomovni . '/' . $cisloOrientacni, '/'));
		$houseNumber = $houseNumber === '0' ? null : $houseNumber;

		$cisloOrientacniPismeno = Strings::trimNull($cisloOrientacniPismeno);
		if ($cisloOrientacniPismeno !== null) {
			$houseNumber .= $cisloOrientacniPismeno;
		}

		return $houseNumber;
	}


	public static function isPerson(int $legalForm): bool
	{
		return match ($legalForm) {
			SubjectType::OSVC, 105, 107, 424, 425 => true,
			default => false,
		};
	}


	public static function normalizeIN(string $in): string
	{
		return str_pad($in, 8, '0', STR_PAD_LEFT);
	}


	/**
	 * @return addressType
	 */
	public static function parseAddress(string $address): array
	{
		/** @var ?addressTypeRaw $results */
		$results = NetteStrings::match($address, '~^(?<street>.+) (?<house_number>\d+(?:/\d+)?(\w)?)(?:, (?<district>.+?))?, (?<zip>\d{5}) (?<city>.+?)(, (?<country>.+))?$~');

		if ($results !== null) {
			return self::prepareAddressData($results);
		}

		/** @var ?addressTypeRaw $results */
		$results = NetteStrings::match($address, '~^(?<city>.+), (?<zip>\d{5})(?:, (?<district>.+?))?, (?<street>.+), (?<house_number>\d+(?:/\d+)?(\w)?)$~');

		return self::prepareAddressData($results ?? []);
	}


	/**
	 * @param addressTypeRaw $results
	 * @return addressType
	 */
	private static function prepareAddressData(array $results): array
	{
		return [
			'zip' => $results['zip'] ?? null,
			'street' => $results['street'] ?? null,
			'house_number' => $results['house_number'] ?? null,
			'city' => $results['city'] ?? null,
			'country' => $results['country'] ?? null,
		];
	}


	public static function normalizeTIN(string $tin): string
	{
		$upper = strtoupper($tin);
		if (str_starts_with($upper, 'CZ') === false && is_numeric($upper)) {
			return "CZ$upper";
		}

		return $upper;
	}
}
