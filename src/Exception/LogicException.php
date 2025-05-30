<?php

declare(strict_types=1);

namespace h4kuna\Ares\Exception;

use Throwable;

final class LogicException extends \LogicException
{
	public function __construct(string $message, ?Throwable $previous = null)
	{
		parent::__construct($message, $previous?->getCode() ?? 0, $previous);
	}
}
