<?php declare(strict_types=1);

namespace h4kuna\Ares\Exception;

final class ResultException extends RuntimeException
{

	public static function withMessage(string $message): self
	{
		return new self($message);
	}
}
