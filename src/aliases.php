<?php declare(strict_types=1);

// keep up to v3.1
namespace h4kuna\Ares\Basic
{

	if (false) {
		/** @deprecated use h4kuna\Ares\Ares\Core\Data */
		class Data
		{
		}
	}
}

namespace h4kuna\Ares
{
	class_alias(Ares\Core\Data::class, 'h4kuna\Ares\Basic\Data');
}
