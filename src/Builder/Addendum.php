<?php

/**
 * This software package is licensed under `AGPL, Commercial` license[s].
 *
 * @package maslosoft/signals
 * @license AGPL, Commercial
 *
 * @copyright Copyright (c) Peter Maselkowski <pmaselkowski@gmail.com>
 *
 */

namespace Maslosoft\Signals\Builder;

use Maslosoft\Addendum\Interfaces\AnnotatedInterface;
use Maslosoft\Addendum\Utilities\AnnotationUtility;
use Maslosoft\Addendum\Utilities\ClassChecker;
use Maslosoft\Addendum\Utilities\FileWalker;
use Maslosoft\Signals\Helpers\DataSorter;
use Maslosoft\Signals\Helpers\NameNormalizer;
use Maslosoft\Signals\Interfaces\ExtractorInterface;
use Maslosoft\Signals\Meta\DocumentMethodMeta;
use Maslosoft\Signals\Meta\DocumentPropertyMeta;
use Maslosoft\Signals\Meta\DocumentTypeMeta;
use Maslosoft\Signals\Meta\SignalsMeta;
use Maslosoft\Signals\Signal;
use ReflectionClass;
use UnexpectedValueException;

/**
 * Addendum extractor
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class Addendum implements ExtractorInterface
{

	// Data keys for annotations extraction
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

	/**
	 * Signals and slots data
	 * @var mixed
	 */
	private $data = [
		Signal::Slots => [
		],
		Signal::Signals => [
		]
	];

	/**
	 * Scanned file paths
	 * @var string[]
	 */
	private $paths = [];

	/**
	 * Annotations mathing patterns
	 * @var string[]
	 */
	private $patterns = [];

	public function __construct()
	{
		$annotations = [
			self::SlotFor,
			self::SignalFor
		];
		foreach ($annotations as $annotation)
		{
			$annotation = preg_replace('~^@~', '', $annotation);
			$this->patterns[] = sprintf('~@%s~', $annotation);
		}
	}

	/**
	 * Get signals and slots data
	 * @return mixed
	 */
	public function getData()
	{
		(new FileWalker([], [$this, 'processFile'], $this->signal->paths))->walk();
		DataSorter::sort($this->data);
		return $this->data;
	}

	/**
	 * Get scanned paths. This is available only after getData call.
	 * @return string[]
	 */
	public function getPaths()
	{
		return $this->paths;
	}

	/**
	 * Set signal instance
	 * @param Signal $signal
	 */
	public function setSignal(Signal $signal)
	{
		$this->signal = $signal;
	}

	/**
	 * @param string $file
	 */
	public function processFile($file, $contents)
	{
		$file = realpath($file);
		$this->paths[] = $file;
		// Remove initial `\` from namespace
		$annotated = AnnotationUtility::rawAnnotate($file);
		$namespace = preg_replace('~^\\\\+~', '', $annotated['namespace']);
		$className = $annotated['className'];


		// Use fully qualified name, class must autoload
		$fqn = $namespace . '\\' . $className;
		NameNormalizer::normalize($fqn);
		$info = new ReflectionClass($fqn);
		$isAnnotated = $info->implementsInterface(AnnotatedInterface::class);
		$hasSignals = $this->hasSignals($contents);
		$isAbstract = $info->isAbstract() || $info->isInterface();

		// Old classes must now implement interface
		// Brake BC!
		if ($hasSignals && !$isAnnotated && !$isAbstract)
		{
			throw new UnexpectedValueException(sprintf('Class %s must implement %s to use signals', $fqn, AnnotatedInterface::class));
		}

		// Skip not annotated class
		if (!$isAnnotated)
		{
			return;
		}

		// Skip abstract classes
		if ($isAbstract)
		{
			return;
		}
		$meta = SignalsMeta::create($fqn);
		/* @var $typeMeta DocumentTypeMeta */
		$typeMeta = $meta->type();

		// Signals
		foreach ($typeMeta->signalFor as $slot)
		{
			$this->data[Signal::Slots][$slot][$fqn] = true;
		}

		// Slots
		// For constructor injection
		foreach ($typeMeta->slotFor as $slot)
		{
			$key = implode('@', [$fqn, '__construct', '()']);
			$this->data[Signal::Signals][$slot][$fqn][$key] = true;
		}

		// For method injection
		foreach ($meta->methods() as $methodName => $method)
		{
			/* @var $method DocumentMethodMeta */
			foreach ($method->slotFor as $slot)
			{
				$key = implode('@', [$fqn, $methodName, '()']);
				$this->data[Signal::Signals][$slot][$fqn][$key] = sprintf('%s()', $methodName);
			}
		}

		// For property injection
		foreach ($meta->fields() as $fieldName => $field)
		{
			/* @var $field DocumentPropertyMeta */
			foreach ($field->slotFor as $slot)
			{
				$key = implode('@', [$fqn, $fieldName]);
				$this->data[Signal::Signals][$slot][$fqn][$key] = sprintf('%s', $fieldName);
			}
		}
	}

	private function hasSignals($contents)
	{
		foreach ($this->patterns as $pattern)
		{
			if (preg_match($pattern, $contents))
			{
				return true;
			}
		}
		return false;
	}

}
