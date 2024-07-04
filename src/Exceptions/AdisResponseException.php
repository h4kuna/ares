<?php declare(strict_types=1);

namespace h4kuna\Ares\Exceptions;

use h4kuna\Ares\Ares\Core\Data;
use Psr\Http\Client\ClientExceptionInterface;

final class AdisResponseException extends AresException implements ClientExceptionInterface
{
	public function __construct(
		public Data $data,
		ServerResponseException $previous
	)
	{
		parent::__construct('Validation by Adis failed, you can use $data from ARES only. Fields vat_payer and tin are not valid.', $previous->getCode(), $previous);
	}
}
