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

/**
 * This class normalize class names into consistent values.
 * @deprecated since version 1.1.4
 * @codeCoverageIgnore
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
