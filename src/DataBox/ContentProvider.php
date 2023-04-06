<?php declare(strict_types=1);

namespace h4kuna\Ares\DataBox;

use Psr\Http\Message\StreamFactoryInterface;

final class ContentProvider
{
	public function __construct(
		private Client $client,
		private StreamFactoryInterface $streamFactory,
	)
	{
	}


	public function load(string $in): \stdClass
	{
		return $this->xml('Ico', $in)->Osoba;
	}


	private function xml(string $parameter, string $value): \stdClass
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
