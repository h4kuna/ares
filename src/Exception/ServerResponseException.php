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
	) {
		$this->code = $code;
		parent::__construct($message, $previous);
	}

	public static function brokenXml(): self
	{
		return self::badResponse('Broken response xml response.');
	}

	public static function badResponse(string $statusText, int $statusCode = 0): self
	{
		return new self($statusText, $statusCode);
	}

	public static function fromException(Throwable $exception): self
	{
		return new self($exception->getMessage(), $exception->getCode(), $exception);
	}

}
