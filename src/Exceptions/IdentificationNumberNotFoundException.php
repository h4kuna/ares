<?php declare(strict_types=1);

namespace h4kuna\Ares\Exceptions;

use Throwable;

final class IdentificationNumberNotFoundException extends AresException
{

	/** @var string */
	private $in;


	/**
	 * @param string $in
	 */
	public function __construct($message = "", $in = '', Throwable $previous = null)
	{
		parent::__construct($message, 0, $previous);
		$this->in = $in;
	}


	public function getIn(): string
	{
		return $this->in;
	}

}
