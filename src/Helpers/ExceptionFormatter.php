<?php

namespace Maslosoft\Signals\Helpers;

use Maslosoft\Addendum\Interfaces\AnnotationInterface;
use ReflectionClass;

/**
 * ExceptionFormatter
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class ExceptionFormatter
{

	public static function formatForAnnotation(AnnotationInterface $annotation, $class)
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
