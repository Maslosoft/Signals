<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
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
