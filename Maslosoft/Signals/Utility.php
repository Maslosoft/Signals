<?php

namespace Maslosoft\Signals;

use CComponent;
use EAnnotationUtility;
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

	private $_data = [];

	public function generate()
	{
		$annotations = [
			'SlotFor',
			'SignalFor'
		];
//		echo '<pre>';
		/**
		 * FIXME This must be configurable
		 */
		$paths = [
			Yii::getPathOfAlias('application'),
			Yii::getPathOfAlias('vendor'),
			Yii::getPathOfAlias('maslosoft'),
		];
		EAnnotationUtility::fileWalker($annotations, [$this, 'processFile'], $paths);

//		var_export($this->_data);
//		echo '</pre>';
		return $this->_data;
	}

	/**
	 * TODO Slot should be annotated at method level and store this method name in definiution file
	 * @param type $file
	 */
	public function processFile($file)
	{
		// Create alias for current file
		$namespace = EAnnotationUtility::rawAnnotate($file)['namespace'];
		$className = EAnnotationUtility::rawAnnotate($file)['className'];
		
		// Remove global `\` namespace
		$namespace = preg_replace('~^\\\\+~', '', $namespace);

		// Create alias or fully namespaced class name
		if(strstr($namespace, '\\'))
		{
			// Use namespaced name, class must autoload
			$alias = $namespace . '\\' . $className;
		}
		else
		{
			// Use alias for non namespaced classes
			$alias = EAnnotationUtility::getAliasOfPath($file);
		}

		// Signals
		$class = EAnnotationUtility::rawAnnotate($file)['class'];
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
		$methods = EAnnotationUtility::rawAnnotate($file)['methods'];
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
		$fields = EAnnotationUtility::rawAnnotate($file)['fields'];
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
