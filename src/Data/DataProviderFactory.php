<?php declare(strict_types=1);

namespace h4kuna\Ares\Data;

use h4kuna\Ares;

class DataProviderFactory
{

	public function create(): DataProvider
	{
		return new DataProvider(new Data());
	}

}
