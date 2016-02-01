<?php

/**
 * This software package is licensed under `AGPL, Commercial` license[s].
 *
 * @package maslosoft/signals
 * @license AGPL, Commercial
 *
 * @copyright Copyright (c) Peter Maselkowski <pmaselkowski@gmail.com>
 *
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
