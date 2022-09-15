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

use Maslosoft\Signals\Meta\SignalsAnnotation;
use ReflectionClass;

/**
 * ExceptionFormatter
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class ExceptionFormatter
{

	public static function formatForAnnotation(SignalsAnnotation $annotation, $class)
	{
		$shortName = (new ReflectionClass($annotation))->getShortName();
		$type = preg_replace('~Annotation$~', '', $shortName);
		$typeName = $annotation->getMeta()->type()->name;
		$name = $annotation->name;

		// Always available params
		$params = [
			$type,
			$typeName,
		];

		// Prepare first part of message
		if (empty($class))
		{
			$msg = 'Could not resolve class name';
		}
		else
		{
			array_unshift($params, $class);
			$msg = 'Class `%s` not found';
		}

		// Prepare location of bogus annotation
		if ($name === $typeName)
		{
			$msgOn = 'for `@%s` annotation on `%s`';
		}
		else
		{
			$params[] = $name;
			$msgOn = 'for `@%s` annotation on `%s::%s`';
		}
		$msg = implode(' ', [$msg, $msgOn]);
		return vsprintf($msg, $params);
	}

}
