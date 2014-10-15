<?php

namespace Maslosoft\Signals;

use CComponent;
use CLogger;
use Maslosoft\Addendum\Utilities\AnnotationUtility;
use Yii;

/**
 * Signals utility class
 *
 * @author Piotr
 */
class Utility extends CComponent
{

	const slotFor = 'SlotFor';
	const signalFor = 'SignalFor';

	private $_data = [
		Signal::slots => [
		],
		Signal::signals => [
		]
	];

	public function generate()
	{
		// Here are string literals instead of consts, because these are annotation names
		$annotations = [
			'SlotFor',
			'SignalFor'
		];
		$paths = [];
		foreach (Yii::app()->signal->searchAliases as $alias)
		{
			$path = Yii::getPathOfAlias($alias);
			if ($path)
			{
				$paths[] = $path;
			}
			else
			{
				Yii::log(sprintf("Alias %s is invalid", $alias), CLogger::LEVEL_WARNING, 'Maslosoft.Signals');
			}
		}
		AnnotationUtility::fileWalker($annotations, [$this, 'processFile'], $paths);
		return $this->_data;
	}

	/**
	 * @param string $file
	 */
	public function processFile($file)
	{
		// Remove initial `\` from namespace
		$namespace = preg_replace('~^\\\\+~', '', AnnotationUtility::rawAnnotate($file)['namespace']);
		$className = AnnotationUtility::rawAnnotate($file)['className'];

		// Use fully qualified name, class must autoload
		$fqn = str_replace('\\\\', '\\', '\\' . $namespace . '\\' . $className);

		// Signals
		$class = AnnotationUtility::rawAnnotate($file)['class'];
		if (isset($class[self::signalFor]))
		{
			$val = $this->_getValuesFor($class[self::signalFor]);
			foreach ($val as $slot)
			{
				$this->_data[Signal::slots][$slot][$fqn] = true;
			}
		}

		// Slots
		// For constructor injection
		if (isset($class[self::slotFor]))
		{
			$val = $this->_getValuesFor($class[self::slotFor]);
			foreach ($val as $slot)
			{
				$this->_data[Signal::signals][$slot][$fqn] = true;
			}
		}

		// For method injection
		$methods = AnnotationUtility::rawAnnotate($file)['methods'];
		foreach ($methods as $methodName => $method)
		{
			if (!isset($method[self::slotFor]))
			{
				continue;
			}
			$val = $this->_getValuesFor($method[self::slotFor]);
			foreach ($val as $slot)
			{
				$this->_data[Signal::signals][$slot][$fqn] = sprintf('%s()', $methodName);
			}
		}

		// For property injection
		$fields = AnnotationUtility::rawAnnotate($file)['fields'];
		foreach ($fields as $fieldName => $method)
		{
			if (!isset($method[self::slotFor]))
			{
				continue;
			}
			$val = $this->_getValuesFor($method[self::slotFor]);
			foreach ($val as $slot)
			{
				$this->_data[Signal::signals][$slot][$fqn] = sprintf('%s', $fieldName);
			}
		}
	}

	private function _getValuesFor($src)
	{
		$value = [];
		foreach ($src as $val)
		{
			if (!array_key_exists('value', $val))
			{
				continue;
			}
			$val = $val['value'];
			if (is_array($val))
			{
				$value = array_merge($value, $val);
			}
			elseif (is_string($val))
			{
				$value[] = $val;
			}
		}
		return array_values(array_unique($value));
	}

}
