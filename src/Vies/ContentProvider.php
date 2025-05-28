<?php declare(strict_types=1);

namespace h4kuna\Ares\Vies;

use h4kuna\Ares\Exception\ServerResponseException;
use Nette\Utils\Strings;
use stdClass;

/**
 * @phpstan-import-type ViesResponse from Client
 */
final class ContentProvider
{
	public function __construct(private Client $client)
	{
	}


	/**
	 * @param string|ViesEntity $vatNumber
	 * @return ViesResponse
	 *
	 * @throws ServerResponseException
	 */
	public function checkVat(string|ViesEntity $vatNumber): object
	{
		if (is_string($vatNumber)) {
			$match = Strings::match($vatNumber, '/(?<country>[A-Z]{2})/');
			if (isset($match['country']) === false) {
				throw new InvalidStateException('Use class ViesEntity instead of string.');
			}

			$viesEntity = new ViesEntity(substr($vatNumber, 2), $match['country']);
		} else {
			$viesEntity = $vatNumber;
		}

		return $this->client->checkVatNumber($viesEntity);
	}


	public function status(): stdClass
	{
		return $this->client->status();
	}
}

