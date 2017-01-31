<?php

/**
 * This software package is licensed under `AGPL, Commercial` license[s].
 *
 * @package maslosoft/signals
 * @license AGPL, Commercial
 *
 * @copyright Copyright (c) Peter Maselkowski <pmaselkowski@gmail.com>
 * @link https://maslosoft.com/signals/
 */

namespace Maslosoft\Signals\Factories;

use Maslosoft\Signals\Signal;

/**
 * FilterFactory
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class FilterFactory
{

	public static function create(Signal $signal, $interface)
	{
		$filters = [];
		$di = $signal->getDi();
		foreach ($signal->filters as $config)
		{
			$filter = $di->apply($config);
			if (!$filter instanceof $interface)
			{
				continue;
			}
			$filters[] = $filter;
		}
		return $filters;
	}

}
