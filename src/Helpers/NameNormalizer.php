<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\Signals\Helpers;

/**
 * This class normalize class names into consistent values.
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class NameNormalizer
{

	public static function normalize(&$className)
	{
		$replaces = [
			'~\\\+~' => '\\',
			'~^\\\~' => '',
			'~\\\$~' => '',
		];
		$className = '\\' . preg_replace(array_keys($replaces), $replaces, $className);
	}

}
