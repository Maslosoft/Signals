<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\Signals\Builder;

use Maslosoft\Addendum\Utilities\AnnotationUtility;
use Maslosoft\Addendum\Utilities\ClassChecker;
use Maslosoft\Signals\Helpers\NameNormalizer;
use Maslosoft\Signals\Interfaces\ExtractorInterface;
use Maslosoft\Signals\Signal;

/**
 * Addendum
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class Addendum implements ExtractorInterface
{

	// Data keys
	const SlotFor = 'SlotFor';
	const SignalFor = 'SignalFor';
	// Default annotation names
	const SlotName = 'SlotFor';
	const SignalName = 'SignalFor';

	/**
	 * Signal instance
	 * @var Signal
	 */
	private $signal = null;
	private $data = [
		Signal::Slots => [
		],
		Signal::Signals => [
		]
	];

	public function getData()
	{
		$annotations = [
			self::SlotFor,
			self::SignalFor
		];
		AnnotationUtility::fileWalker($annotations, [$this, 'processFile'], $this->signal->paths);
		return $this->data;
	}

	public function setSignal(Signal $signal)
	{
		$this->signal = $signal;
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
				$this->data[Signal::Slots][$slot][$fqn] = true;
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
				$this->data[Signal::Signals][$slot][$fqn][] = true;
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
				$this->data[Signal::Signals][$slot][$fqn][] = sprintf('%s()', $methodName);
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
				$this->data[Signal::Signals][$slot][$fqn][] = sprintf('%s', $fieldName);
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
