<?php

namespace h4kuna\Ares;

use Nette;

/**
 * @author Milan Matějček <milan.matejcek@gmail.com>
 */
class Ares extends Nette\Object
{

	/** @var IRequest */
	private $request;

	public function __construct(IRequest $request = NULL)
	{
		if ($request === NULL) {
			$request = new Get();
		}
		$this->request = $request;
	}

	/**
	 * Load fresh data.
	 * @param int|string $inn
	 * @return Data
	 */
	public function loadData($inn)
	{
		$this->request->clean();
		return $this->request->loadData($inn);
	}

	/**
	 * Get temporary data.
	 * @return Data
	 */
	public function getData()
	{
		return $this->request->loadData();
	}

}
