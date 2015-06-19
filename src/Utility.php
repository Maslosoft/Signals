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

namespace Maslosoft\Signals;

use Maslosoft\Addendum\Utilities\AnnotationUtility;
use Maslosoft\Addendum\Utilities\ClassChecker;
use Maslosoft\Addendum\Utilities\NameNormalizer;
use Maslosoft\Cli\Shared\Helpers\PhpExporter;

/**
 * Signals utility class
 *
 * @author Piotr
 */
class Utility
{

	const SlotFor = 'SlotFor';
	const SignalFor = 'SignalFor';

	private $_data = [
		Signal::Slots => [
		],
		Signal::Signals => [
		]
	];

	/**
	 * Signal instance
	 * @var Signal
	 */
	private $signal = null;

	public function __construct(Signal $signal)
	{
		$this->signal = $signal;
	}

	public function generate()
	{
		// Here are string literals instead of consts, because these are annotation names
		$annotations = [
			'SlotFor',
			'SignalFor'
		];
		AnnotationUtility::fileWalker($annotations, [$this, 'processFile'], $this->signal->paths);
		$data = PhpExporter::export($this->_data, 'Auto generated, any changes will be lost');

		$path = sprintf("%s/%s", $this->signal->runtimePath, $this->signal->configFilename);
		file_put_contents($path, $data);
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
		$fqn = $namespace . '\\' . $className;
		NameNormalizer::normalize($fqn);

		// Signals
		$class = AnnotationUtility::rawAnnotate($file)['class'];
		if (isset($class[self::SignalFor]))
		{
			$val = $this->getValuesFor($class[self::SignalFor]);
			foreach ($val as $slot)
			{
				NameNormalizer::normalize($slot);
				$this->checkClass($slot, $fqn);
				$this->_data[Signal::Slots][$slot][$fqn] = true;
			}
		}

		// Slots
		// For constructor injection
		if (isset($class[self::SlotFor]))
		{
			$val = $this->getValuesFor($class[self::SlotFor]);
			foreach ($val as $slot)
			{
				NameNormalizer::normalize($slot);
				$this->checkClass($slot, $fqn);
				$this->_data[Signal::Signals][$slot][$fqn][] = true;
			}
		}

		// For method injection
		$methods = AnnotationUtility::rawAnnotate($file)['methods'];
		foreach ($methods as $methodName => $method)
		{
			if (!isset($method[self::SlotFor]))
			{
				continue;
			}
			$val = $this->getValuesFor($method[self::SlotFor]);
			foreach ($val as $slot)
			{
				NameNormalizer::normalize($slot);
				$this->checkClass($slot, $fqn);
				$this->_data[Signal::Signals][$slot][$fqn][] = sprintf('%s()', $methodName);
			}
		}

		// For property injection
		$fields = AnnotationUtility::rawAnnotate($file)['fields'];
		foreach ($fields as $fieldName => $method)
		{
			if (!isset($method[self::SlotFor]))
			{
				continue;
			}
			$val = $this->getValuesFor($method[self::SlotFor]);
			foreach ($val as $slot)
			{
				NameNormalizer::normalize($slot);
				$this->checkClass($slot, $fqn);
				$this->_data[Signal::Signals][$slot][$fqn][] = sprintf('%s', $fieldName);
			}
		}
	}

	private function getValuesFor($src)
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

	private function checkClass($slot, $fqn)
	{
		if (!ClassChecker::exists($fqn))
		{
			$this->signal->getLogger()->warning(sprintf("Class `%s` not found while generating signal data for `%s`", $fqn, $slot));
		}
	}

}
