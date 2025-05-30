<?php declare(strict_types=1);

namespace h4kuna\Ares\DataBox;

use h4kuna\Ares\Exception\ResultException;
use h4kuna\Ares\Exception\ServerResponseException;
use h4kuna\Ares\Tool\Arrays;
use Psr\Http\Message\StreamFactoryInterface;
use stdClass;

class ContentProvider
{
	public function __construct(
		private Client $client,
		private StreamFactoryInterface $streamFactory,
	) {
	}

	/**
	 * @return list<stdClass>
	 * @throws ResultException
	 * @throws ServerResponseException
	 */
	public function load(string $in): array
	{
		$content = $this->xml('Ico', $in)->Osoba;

		return Arrays::fromStdClass($content);
	}

	/**
	 * @throws ResultException
	 * @throws ServerResponseException
	 */
	protected function xml(string $parameter, string $value): stdClass
	{
		$xml = <<<XML
		<GetInfoRequest xmlns="http://seznam.gov.cz/ovm/ws/v1">
			<$parameter>$value</$parameter>
		</GetInfoRequest>
		XML;

		return $this->client->request(
			$this->streamFactory->createStream($xml),
		);
	}

}
