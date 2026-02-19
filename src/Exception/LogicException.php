<?php declare(strict_types = 1);

namespace h4kuna\Ares\Exception;

use LogicException as CoreLogicException;
use Throwable;

final class LogicException extends CoreLogicException
{

	public function __construct(
		string $message,
		?Throwable $previous = null,
	)
	{
		parent::__construct($message, $previous?->getCode() ?? 0, $previous);
	}

}
