<?php declare(strict_types=1);

namespace h4kuna\Ares;

final class Error
{
	/** @var string */
	private $in;

	/** @var int */
	private $code;

	/** @var string */
	private $message;


	public function __construct(string $in, int $code, string $message)
	{
		$this->in = $in;
		$this->code = $code;
		$this->message = $message;
	}


	public function getCode(): int
	{
		return $this->code;
	}


	public function getMessage(): string
	{
		return $this->message;
	}


	public function getIn(): string
	{
		return $this->in;
	}


	public function toArray(): array
	{
		return [
			'in' => $this->in,
			'code' => $this->code,
			'message' => $this->message,
		];
	}

}
