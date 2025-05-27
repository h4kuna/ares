<?php declare(strict_types=1);

namespace h4kuna\Ares\Ares\Core;

use Generator;
use h4kuna\Ares\Adis;
use h4kuna\Ares\Ares\Client;
use h4kuna\Ares\Ares\Helper;
use h4kuna\Ares\Ares\Sources;
use h4kuna\Ares\Exception\AdisResponseException;
use h4kuna\Ares\Exception\IdentificationNumberNotFoundException;
use h4kuna\Ares\Exception\ResultException;
use h4kuna\Ares\Exception\ServerResponseException;
use h4kuna\Ares\Tool\Batch;

final class ContentProvider
{
	private const BATCH = 100; // max identification numbers per request

	public function __construct(
		private JsonToDataTransformer $jsonTransformer,
		private Client $client,
		private Adis\ContentProvider $adisContentProvider,
	) {
	}

	public function getClient(): Client
	{
		return $this->client;
	}

	/**
	 * @template KeyName
	 * @param array<KeyName, string|int> $identificationNumbers
	 * @return Generator<(int&KeyName)|(KeyName&string), Data>
	 *
	 * @throws ResultException
	 * @throws ServerResponseException
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

			try {
				$subjects = iterator_to_array($this->adisContentProvider->statusBusinessSubjects($map));
			} catch (ServerResponseException) {
				$subjects = [];
			}

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
	 * @throws AdisResponseException
	 * @throws IdentificationNumberNotFoundException
	 * @throws ServerResponseException
	 */
	public function load(string $in): Data
	{
		$json = $this->client->useEndpoint(Sources::CORE, $in);

		$data = $this->jsonTransformer->transform($json);
		if ($data->tin !== null) {
			try {
				$adis = $this->adisContentProvider->statusBusinessSubject($data->tin);
			} catch (ServerResponseException $e) {
				throw AdisResponseException::fromServerException($data, $e);
			}

			$data->setAdis($adis);
		}

		return $data;
	}

}
