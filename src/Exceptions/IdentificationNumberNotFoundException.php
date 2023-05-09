<?php declare(strict_types=1);

namespace h4kuna\Ares\Exceptions;

use Nette\Utils\Strings;
use Throwable;

final class IdentificationNumberNotFoundException extends AresException
{

	private string $in;


	public function __construct(string $message = '', string $in = '', Throwable $previous = null)
	{
        $match = Strings::match($message, '/Chyba +(?<code>\d+)/');

		parent::__construct($message, intval($match['code'] ?? 0), $previous);
		$this->in = $in;
	}


	public function getIn(): string
	{
		return $this->in;
	}

}
