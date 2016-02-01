<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
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
