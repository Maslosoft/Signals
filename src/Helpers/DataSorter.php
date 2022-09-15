<?php

/**
 * Wireless Cross-Component Communication
 *
 * This software package is licensed under `AGPL-3.0-only, proprietary` license[s].
 *
 * @package maslosoft/signals
 * @license AGPL-3.0-only, proprietary
 *
 * @copyright Copyright (c) Peter Maselkowski <pmaselkowski@gmail.com>
 * @link https://maslosoft.com/signals/
 */

namespace Maslosoft\Signals\Helpers;

use Maslosoft\Signals\Signal;

/**
 * DataSorter
 * @codeCoverageIgnore
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
