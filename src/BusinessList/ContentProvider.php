<?php declare(strict_types=1);

namespace h4kuna\Ares\BusinessList;

use h4kuna\Ares\Exceptions\IdentificationNumberNotFoundException;
use h4kuna\Ares\Http\AresRequestProvider;
use h4kuna\Ares\Http\RequestProvider;
use h4kuna\Ares\Tools\Utils;
use Nette\SmartObject;
use Nette\Utils\Json;
use Nette\Utils\Strings;

final class ContentProvider
{
	use SmartObject;

	/**
	 * @var array<callable>
	 */
	public array $onAfterContent = [];


	public function __construct(private AresRequestProvider $requestProvider)
	{
		$this->onAfterContent[] = fn (\stdClass $content) => self::fixContent($content);
	}


	/**
	 * @throws IdentificationNumberNotFoundException
	 */
	public function load(string $in): \stdClass
	{
		$answer = $this->requestProvider->businessList($in);

		$data = RequestProvider::toJson($answer);

		$this->onAfterContent($data);

		if (isset($data->Vypis_OR->ZAU->PFO->KPF)) {
			$data->Vypis_OR->ZAU->PFO->KPF = intval($data->Vypis_OR->ZAU->PFO->KPF);
		}

		return $data->Vypis_OR;
	}


	private static function fixContent(\stdClass $data): void
	{
		self::walk($data->Vypis_OR, function ($value) {
			return Strings::replace(trim($value), '/ {2,}/', ' ');
		});

		Utils::nameAddress($data->Vypis_OR->SO ?? null, 'CSO');
		Utils::nameAddress($data->Vypis_OR->SSV ?? null, 'SS');
		Utils::nameAddress($data->Vypis_OR->DR ?? null, 'CDR');
		Utils::nameAddress($data->Vypis_OR->AKI ?? null, 'AKR');
		Utils::nameAddress($data->Vypis_OR->PRO ?? null, 'PRA');
		Utils::nameAddress($data->Vypis_OR->SOK ?? null, 'CSK');
		Utils::nameAddress($data->Vypis_OR->LI ?? null, 'LIR');

		Utils::nameAddress($data->Vypis_OR->KME ?? null, 'KMA');
		Utils::nameAddress($data->Vypis_OR->KPI ?? null, 'KPR');

		// o.s. / k.s.
		Utils::address($data->Vypis_OR->ZAU->SI);
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
