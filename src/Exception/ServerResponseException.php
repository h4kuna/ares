<?php declare(strict_types=1);

namespace h4kuna\Ares\Exception;

use Throwable;

final class ServerResponseException extends RuntimeException
{

	/**
	 * @param string $message
	 */
	public function __construct(
		$message = 'The service is probably overloaded. Repeat request after a few minutes.',
		int $code = 0,
		?Throwable $previous = null,
	)
	{
		$this->code = $code;
		parent::__construct($message, $previous);
	}

}
