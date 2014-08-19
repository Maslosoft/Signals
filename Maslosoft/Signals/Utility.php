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
		$annotations = [
			'SlotFor',
			'SignalFor'
		];
		$paths = [];
		foreach(Yii::app()->signal->searchAliases as $alias)
		{
			$path = Yii::getPathOfAlias($alias);
			if($path)
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
		// Create alias for current file
		$namespace = AnnotationUtility::rawAnnotate($file)['namespace'];
		$className = AnnotationUtility::rawAnnotate($file)['className'];
		
		// Remove global `\` namespace
		$namespace = preg_replace('~^\\\\+~', '', $namespace);

		// Create alias or fully namespaced class name
		/**
		 * TODO Investigate this case, this is only workaround
		 */
		if(!is_string($namespace))
		{
//			var_dump($file);
			return false;
		}
		if(strstr($namespace, '\\'))
		{
			// Use namespaced name, class must autoload
			$alias = $namespace . '\\' . $className;
		}
		else
		{
			// Use alias for non namespaced classes
			$alias = AnnotationUtility::getAliasOfPath($file);
		}

		// Signals
		$class = AnnotationUtility::rawAnnotate($file)['class'];
		if (isset($class[self::signalFor]))
		{
			$val = $this->_getValuesFor($class[self::signalFor]);
			foreach ($val as $slot)
			{
				$this->_data[Signal::slots][$slot][$alias] = true;
			}
		}

		// Slots
		// For constructor injection
		if (isset($class[self::slotFor]))
		{
			$val = $this->_getValuesFor($class[self::slotFor]);
			foreach ($val as $slot)
			{
				$this->_data[Signal::signals][$slot][$alias] = true;
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
				$this->_data[Signal::signals][$slot][$alias] = sprintf('%s()', $methodName);
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
				$this->_data[Signal::signals][$slot][$alias] = sprintf('%s', $fieldName);
			}
		}
	}

	private function _getValuesFor($src)
	{
		$value = [];
		foreach ($src as $val)
		{
			if(!isset($val['value']))
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

	private function _getAlias($val, $file)
	{
		
		return $alias;
	}

}
