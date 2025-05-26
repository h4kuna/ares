<?php declare(strict_types=1);

namespace h4kuna\Ares\Exceptions;

use Throwable;

final class IdentificationNumberNotFoundException extends AresException
{

	private string $in;


	public function __construct(string $message = '', string $in = '', ?Throwable $previous = null)
	{
		parent::__construct($message, $previous === null ? 0 : $previous->getCode(), $previous);
		$this->in = $in;
	}


	public function getIn(): string
	{
		return $this->in;
	}

}
