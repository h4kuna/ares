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
		$data->tin = Strings::trimNull($json->dic ?? null);
		$data->sources = Helper::services((array) ($json->seznamRegistraci ?? []));

		$data->vat_payer = $data->sources[Sources::SER_NO_DPH] === true;
		$data->company = Strings::trimNull($json->obchodniJmeno ?? null);

		$data->zip = Strings::trimNull((string) ($json->sidlo->psc ?? '')); // input is int
		$data->street = Strings::trimNull($json->sidlo->nazevUlice ?? null);
		$data->country = Strings::trimNull($json->sidlo->nazevStatu ?? null);
		$data->country_code = Strings::trimNull($json->sidlo->kodStatu ?? null);
		$data->street = Strings::trimNull($json->sidlo->nazevUlice ?? null);
		$data->city = Strings::trimNull($json->sidlo->nazevObce ?? null);
		$data->city_post = Strings::trimNull($json->sidlo->nazevMestskeCastiObvodu ?? null);
		$data->city_district = Strings::trimNull($json->sidlo->nazevOkresu ?? $json->sidlo->nazevCastiObce ?? null);
		$data->house_number = Helper::houseNumber((string) ($json->sidlo->cisloDomovni ?? ''), (string) ($json->sidlo->cisloOrientacni ?? ''), $json->sidlo->cisloOrientacniPismeno ?? '');

		if ($data->zip === null && $data->street === null && $data->house_number === null && $data->city === null && isset($json->sidlo->textovaAdresa)) {
			[
				'zip' => $data->zip,
				'street' => $data->street,
				'house_number' => $data->house_number,
				'city' => $data->city,
			] = Helper::parseAddress($json->sidlo->textovaAdresa);
		}

		$data->nace = (array) ($json->czNace ?? []);
		$data->legal_form_code = (int) $json->pravniForma;
		$data->is_person = Helper::isPerson($data->legal_form_code);

		assert($json->datumVzniku !== null);
		$data->created = Strings::createDateTime($json->datumVzniku);
		$data->dissolved = Strings::createDateTime($json->datumZaniku ?? null);
		$data->active = $data->dissolved === null;

		return $data;
	}

}
