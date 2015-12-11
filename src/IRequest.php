<?php

namespace h4kuna\Ares;

/**
 * @author Milan Matějček
 */
interface IRequest
{

	/**
	 * Load data from ares.
	 * @param string $in Identification Number
	 * @return Data
	 */
	public function loadData($in = NULL);

	/**
	 * Clean last request.
	 * @void
	 */
	public function clean();
}
