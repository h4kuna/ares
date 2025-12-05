<?php declare(strict_types=1);

namespace h4kuna\Ares\Ares\Core;

use h4kuna\Ares\Ares\Helper;
use h4kuna\Ares\Ares\Sources;
use h4kuna\Ares\Tool\Strings;
use stdClass;

class JsonToDataTransformer
{
	private const RegisterPriority = ['rzp', 'res', 'vr'];


	public function transform(stdClass $json): Data
	{
		$data = new Data();
		$data->original = $json;

		$data->in = (string) $json->ico;
		$tinGroup = Strings::trimNull($json->dicSkDph ?? null);
		$tinGroup = $tinGroup === 'N/A' ? null : $tinGroup;
		$data->tin = Strings::trimNull($tinGroup ?? $json->dic ?? null);
		$data->sources = Helper::services((array) ($json->seznamRegistraci ?? []));

		$data->vat_payer = $data->sources[Sources::SER_NO_DPH] === true;
		$data->company = Strings::trimNull($json->obchodniJmeno ?? null);

		self::resolveAddress($data, $json);

		$data->nace = (array) ($json->czNace ?? []);
		$data->legal_form_code = isset($json->pravniForma) ? (int) $json->pravniForma : null;
		$data->legal_form_code_ros = isset($json->pravniFormaRos) ? (int) $json->pravniFormaRos : null;

		$data->is_person = self::resolveIsPerson($data->legal_form_code, $data->legal_form_code_ros);

		$data->created = Strings::createDateTime($json->datumVzniku ?? null);
		$data->dissolved = Strings::createDateTime($json->datumZaniku ?? null);
		$data->active = $data->dissolved === null;

		return $data;
	}


	private static function updateAddress(Data $data, stdClass $sidlo): bool
	{
		$data->zip = Strings::trimNull(Strings::replaceSpace((string) ($sidlo->psc ?? $sidlo->pscTxt ?? ''))); // input is int
		$data->street = Strings::trimNull($sidlo->nazevUlice ?? null);
		$data->country = Strings::trimNull($sidlo->nazevStatu ?? null);
		$data->country_code = Strings::trimNull($sidlo->kodStatu ?? null);
		$data->city = Strings::trimNull($sidlo->nazevObce ?? null);
		$data->city_post = Strings::trimNull($sidlo->nazevMestskeCastiObvodu ?? null);
		$data->city_district = Strings::trimNull($sidlo->nazevCastiObce ?? null);
		$data->district = Strings::trimNull($sidlo->nazevOkresu ?? null);
		$data->house_number = Helper::houseNumber((string) ($sidlo->cisloDomovni ?? $sidlo->cisloDoAdresy ?? ''), (string) ($sidlo->cisloOrientacni ?? ''), $sidlo->cisloOrientacniPismeno ?? '');

		return self::isAddressFilled($data);
	}


	private static function resolveAddress(Data $data, stdClass $json): void
	{
		$addressExists = isset($json->sidlo) && self::updateAddress($data, $json->sidlo);

		if ($addressExists === false) {
			$additionalData = isset($json->dalsiUdaje) ? self::prepareForAddress($json->dalsiUdaje) : [];
			if ($additionalData !== []) {
				foreach (self::RegisterPriority as $register) {
					$key = self::keyForAddress($register, $json);
					if (isset($additionalData[$key])) {
						$addressExists = self::updateAddress($data, $additionalData[$key]);
						if ($addressExists === true) {
							break;
						}
					}
				}
			}
		}

		if ($addressExists === false && isset($json->sidlo->textovaAdresa)) {
			[
				'zip' => $data->zip,
				'street' => $data->street,
				'house_number' => $data->house_number,
				'city' => $data->city,
				'country' => $country,
			] = Helper::parseAddress($json->sidlo->textovaAdresa);
			$data->country ??= $country;
		}
	}


	private static function isAddressFilled(Data $data): bool
	{
		if ($data->zip === null) {
			return false;
		}

		return $data->street !== null
			|| $data->country !== null
			|| $data->country_code !== null
			|| $data->city !== null
			|| $data->city_post !== null
			|| $data->city_district !== null
			|| $data->district !== null
			|| $data->house_number !== null;
	}


	/**
	 * @param array<stdClass> $dalsiUdaje
	 * @return array<stdClass>
	 */
	private static function prepareForAddress(array $dalsiUdaje): array
	{
		$out = [];
		foreach ($dalsiUdaje as $json) {
			$key = self::keyForAddress($json->datovyZdroj, $json);
			foreach ($json->sidlo ?? [] as $sidlo) {
				if ($sidlo?->primarniZaznam === true && isset($sidlo->sidlo)) {
					$out[$key] = $sidlo->sidlo;
					break;
				}
			}
		}

		return $out;
	}


	private static function keyForAddress(string $datovyZdroj, stdClass $json): string
	{
		$pravniForma = $json->pravniForma ?? (isset($json->pravniFormaRos) ? ($json->pravniFormaRos . 'ROS') : '0');
		return "$datovyZdroj|$pravniForma";
	}


	private static function resolveIsPerson(?int $pravniForma, ?int $pravniFormaRos): bool
	{
		if ($pravniForma === null && $pravniFormaRos === null) {
			return false;
		} elseif ($pravniForma !== null && $pravniFormaRos !== null) {
			return Helper::isPerson($pravniForma);
		} elseif ($pravniForma !== null) {
			return Helper::isPerson($pravniForma);
		}

		return Helper::isPerson($pravniFormaRos);
	}

}
