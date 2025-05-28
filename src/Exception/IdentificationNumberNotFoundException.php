<?php declare(strict_types=1);

namespace h4kuna\Ares\Exception;

use Throwable;

final class IdentificationNumberNotFoundException extends RuntimeException
{

	private string $in;


	public function __construct(string $message = '', string $in = '', ?Throwable $previous = null)
	{
		parent::__construct($message, $previous);
		$this->in = $in;
	}


	public function getIn(): string
	{
		return $this->in;
	}

}
