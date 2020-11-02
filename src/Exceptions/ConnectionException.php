<?php declare(strict_types=1);

namespace h4kuna\Ares\Exceptions;

final class ConnectionException extends \RuntimeException
{

	/**
	 * @param string $message
	 * @param int $code
	 */
	public function __construct(
		$message = 'The Ares probably is overloaded. Repeat request after few minutes.',
		$code = 0,
		\Throwable $previous = null
	)
	{
		parent::__construct($message, $code, $previous);
	}

}
