<?php

namespace h4kuna\Ares;

class DataFactory implements IDataFactory
{

	public function create()
	{
		return new Data();
	}

}
