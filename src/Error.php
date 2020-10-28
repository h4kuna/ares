<?php declare(strict_types=1);

namespace h4kuna\Ares;

class Error
{
	
	/** @var int */
	protected $code;
	
	/** @var string */
	protected $message;

	/**
	 * Error constructor.
	 * @param int $code
	 * @param string $message
	 */
	public function __construct(int $code, string $message)
	{
		$this->code = $code;
		$this->message = $message;
	}

	/**
	 * @return int
	 */
	public function getCode(): int
	{
		return $this->code;
	}

	/**
	 * @return string
	 */
	public function getMessage(): string
	{
		return $this->message;
	}

	public function toArray(): array
	{
		return [
			'code' => $this->getCode(),
			'message' => $this->getMessage(),
		];
	}
	
}
