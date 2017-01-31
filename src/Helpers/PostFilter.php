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

namespace Maslosoft\Signals\Helpers;

use Maslosoft\Signals\Interfaces\PostFilterInterface;
use Maslosoft\Signals\Signal;

/**
 * PostFilter
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class PostFilter
{

	public static function filter(Signal $signals, $injected, $signal)
	{
		$filters = $signals->getFilters(PostFilterInterface::class);
		foreach ($filters as $filter)
		{
			if (!$filter->filter($injected, $signal))
			{
				return false;
			}
		}
		return true;
	}

}
