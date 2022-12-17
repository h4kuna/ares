<?php declare(strict_types=1);

namespace h4kuna\Ares;

/**
 * The class will be readonly in future php version.
 */
final class Error
{
	public string $in;

	public int $code;

	public string $message;


	public function __construct(string $in, int $code, string $message)
	{
		$this->in = $in;
		$this->code = $code;
		$this->message = $message;
	}


	/**
	 * @return array{in: string, code: int, message: string}
	 */
	public function toArray(): array
	{
		return [
			'in' => $this->in,
			'code' => $this->code,
			'message' => $this->message,
		];
	}

}
