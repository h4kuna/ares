<?php declare(strict_types=1);

namespace h4kuna\Ares\Ares\Core;

use Generator;
use h4kuna\Ares\Adis;
use h4kuna\Ares\Ares\Client;
use h4kuna\Ares\Ares\Helper;
use h4kuna\Ares\Ares\Sources;
use h4kuna\Ares\Exceptions\IdentificationNumberNotFoundException;
use h4kuna\Ares\Exceptions\ServerResponseException;
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


	public function getClient(): Client
	{
		return $this->client;
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
		$json = $this->client->useEndpoint(Sources::CORE, $in);

		$data = $this->jsonTransformer->transform($json);
		if ($data->tin !== null) {
			try {
				$adis = $this->adisContentProvider->statusBusinessSubject($data->tin);
				$data->setAdis($adis);
			} catch (ServerResponseException) {
				// intentionally silent
				// @see $data->isValidByAdis()
			}
		}

		return $data;
	}

}
