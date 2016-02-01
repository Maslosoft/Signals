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

use Maslosoft\Signals\Signal;

/**
 * DataSorter
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class DataSorter
{

	public static function sort(&$data)
	{
		self::_sort($data[Signal::Signals]);
		self::_sort($data[Signal::Slots]);
	}

	private static function _sort(&$data)
	{
		ksort($data);
		foreach ($data as &$values)
		{
			if (is_array($values))
			{
				ksort($values);
			}
		}
	}

}
