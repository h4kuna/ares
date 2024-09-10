<?php declare(strict_types=1);

namespace h4kuna\Ares\Ares\Core;

use h4kuna\Ares\Ares\Helper;
use h4kuna\Ares\Ares\Sources;
use h4kuna\Ares\Tools\Strings;
use stdClass;

class JsonToDataTransformer
{

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

		$addressExists = isset($json->sidlo) && self::updateAddress($data, $json->sidlo);

		if ($addressExists === false && isset($json->dalsiUdaje[0]->sidlo[0]->sidlo)) {
			$addressExists = self::updateAddress($data, $json->dalsiUdaje[0]->sidlo[0]->sidlo);
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

		$data->nace = (array) ($json->czNace ?? []);
		$data->legal_form_code = (int) $json->pravniForma;
		$data->is_person = Helper::isPerson($data->legal_form_code);

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


	private static function isAddressFilled(Data $data): bool
	{
		return $data->zip !== null
			|| $data->street !== null
			|| $data->country !== null
			|| $data->country_code !== null
			|| $data->city !== null
			|| $data->city_post !== null
			|| $data->city_district !== null
			|| $data->district !== null
			|| $data->house_number !== null;
	}

}
