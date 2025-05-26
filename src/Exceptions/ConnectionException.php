<?php declare(strict_types=1);

namespace h4kuna\Ares\Exceptions;

use Psr\Http\Client\ClientExceptionInterface;

/**
 * @deprecated see
 * @see ServerResponseException
 */
class ConnectionException extends \RuntimeException implements ClientExceptionInterface
{

	/**
	 * @param string $message
	 * @param int $code
	 */
	public function __construct(
		$message = 'The service is probably overloaded. Repeat request after a few minutes.',
		$code = 0,
		?\Throwable $previous = null,
	)
	{
		parent::__construct($message, $code, $previous);
	}

}
