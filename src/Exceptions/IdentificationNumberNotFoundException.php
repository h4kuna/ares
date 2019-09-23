<?php declare(strict_types=1);

namespace h4kuna\Ares\Exceptions;

use Throwable;

class IdentificationNumberNotFoundException extends AresException
{

	/** @var int */
	private $in;


	public function __construct($message = "", $in = 0, Throwable $previous = null)
	{
		parent::__construct($message, 0, $previous);
		$this->in = $in;
	}


	public function getIn(): int
	{
		return $this->in;
	}

}