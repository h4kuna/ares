<?php declare(strict_types=1);

namespace h4kuna\Ares\Adis\StatusBusinessSubjects;

use stdClass;

class StatusBusinessSubjectsTransformer
{

	public function transform(stdClass $data): Subject
	{
		$attributes = '@attributes';

		$exists = $data->$attributes->typSubjektu !== 'NENALEZEN';
		$isVatPayer = $data->$attributes->typSubjektu === 'PLATCE_DPH';
		return new Subject(
			$exists,
			$data->$attributes->typSubjektu,
			$data->$attributes->dic,
			$exists && $isVatPayer ? $data->$attributes->nespolehlivyPlatce !== 'ANO' : null,
			$isVatPayer,
			$data->$attributes->cisloFu ?? '',
			$data->adresa ?? null
		);
	}
}
