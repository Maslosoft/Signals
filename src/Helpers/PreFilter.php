<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\Signals\Helpers;

use Maslosoft\Signals\Interfaces\PreFilterInterface;
use Maslosoft\Signals\Interfaces\SignalInterface;
use Maslosoft\Signals\Signal;

/**
 * PreFilter
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class PreFilter
{

	public static function filter(Signal $signals, $fqn, SignalInterface $signal)
	{
		$preFilters = $signals->getFilters(PreFilterInterface::class);
		foreach ($preFilters as $filter)
		{
			if (!$filter->filter($fqn, $signal))
			{
				return false;
			}
		}
		return true;
	}

}
