<?php

/**
 * This software package is licensed under GNU LESSER GENERAL PUBLIC LICENSE license.
 *
 * @package maslosoft/signals
 * @licence GNU LESSER GENERAL PUBLIC LICENSE
 *
 * @copyright Copyright (c) Peter Maselkowski <pmaselkowski@gmail.com>
 *
 */

namespace Maslosoft\Signals\Helpers;

/**
 * This class normalize class names into consistent values.
 * @deprecated since version 1.1.4
 * NOTE Use NameNormalize from addendum project
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
