<?php

namespace h4kuna\Ares;

class DataFactory implements IDataFactory
{

	public function create(array $data)
	{
		return new Data($data);
	}

}
