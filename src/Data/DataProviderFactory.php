<?php declare(strict_types=1);

namespace h4kuna\Ares\Data;

class DataProviderFactory
{

	public function create(): DataProvider
	{
		return new DataProvider(new Data());
	}

}
