<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\Signals\Helpers;

use Maslosoft\Signals\Interfaces\PostFilterInterface;
use Maslosoft\Signals\Interfaces\SignalInterface;
use Maslosoft\Signals\Signal;

/**
 * PostFilter
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class PostFilter
{

	public static function filter(Signal $signals, $injected, SignalInterface $signal)
	{
		$preFilters = $signals->getFilters(PostFilterInterface::class);
		foreach ($preFilters as $filter)
		{
			if (!$filter->filter($injected, $signal))
			{
				return false;
			}
		}
		return true;
	}

}
