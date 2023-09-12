<?php declare(strict_types=1);

namespace h4kuna\Ares\Ares\Core;

use Generator;
use h4kuna\Ares\Adis;
use h4kuna\Ares\Ares\Client;
use h4kuna\Ares\Ares\Helper;
use h4kuna\Ares\Ares\Sources;
use h4kuna\Ares\Exceptions\IdentificationNumberNotFoundException;
use h4kuna\Ares\Tools\Batch;

final class ContentProvider
{
	private const BATCH = 100; // max identification numbers per request


	public function __construct(
		private JsonToDataTransformer $jsonTransformer,
		private Client $client,
		private Adis\ContentProvider $adisContentProvider,
	)
	{
	}


	/**
	 * @template KeyName
	 * @param array<KeyName, string|int> $identificationNumbers
	 * @return Generator<(int&KeyName)|(KeyName&string), Data>
	 */
	public function loadByIdentificationNumbers(array $identificationNumbers): Generator
	{
		$duplicity = Batch::checkDuplicities($identificationNumbers, fn (string $in) => Helper::normalizeIN($in));
		$chunks = Batch::chunk($duplicity, self::BATCH);

		foreach ($chunks as $INs) {
			$responseData = $this->client->searchEndpoint(Sources::CORE, [
				'ico' => $INs,
				'pocet' => self::BATCH,
			])->ekonomickeSubjekty ?? [];

			$results = $map = [];
			foreach ($responseData as $item) {
				$data = $this->jsonTransformer->transform($item);
				$results[] = $data;
				if ($data->tin !== null) {
					$map[$data->in] = $data->tin;
				}
			}

			$subjects = iterator_to_array($this->adisContentProvider->statusBusinessSubjects($map));

			foreach ($results as $data) {
				foreach ($duplicity[$data->in] as $name) {
					if (isset($subjects[$data->in])) {
						$data->setAdis($subjects[$data->in]);
					}
					yield $name => $data;
				}
			}
		}
	}


	/**
	 * @throws IdentificationNumberNotFoundException
	 */
	public function load(string $in): Data
	{
		try {
			$json = $this->client->useEndpoint(Sources::CORE, $in);
		} catch (IdentificationNumberNotFoundException) {
			$json = $this->client->useEndpoint(Sources::SERVICE_RES, $in);
			$records = $json->zaznamy ?? [];
			$record = reset($records);

			if ($record === null) {
				throw new IdentificationNumberNotFoundException(sprintf('Api: %s.', Sources::SERVICE_RES), (string) $json->icoId);
			}

			return $this->jsonTransformer->transform($record);
		}
		$data = $this->jsonTransformer->transform($json);
		if ($data->tin !== null) {
			$adis = $this->adisContentProvider->statusBusinessSubject($data->tin);
			$data->setAdis($adis);
		}

		return $data;
	}

}
