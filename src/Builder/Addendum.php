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

namespace Maslosoft\Signals\Builder;

use Exception;
use Maslosoft\Addendum\Exceptions\ParseException;
use Maslosoft\Addendum\Interfaces\AnnotatedInterface;
use Maslosoft\Addendum\Utilities\AnnotationUtility;
use Maslosoft\Addendum\Utilities\ClassChecker;
use Maslosoft\Addendum\Utilities\FileWalker;
use Maslosoft\Addendum\Utilities\NameNormalizer;
use Maslosoft\Signals\Exceptions\ClassNotFoundException;
use Maslosoft\Signals\Helpers\DataSorter;
use Maslosoft\Signals\Interfaces\ExtractorInterface;
use Maslosoft\Signals\Meta\DocumentMethodMeta;
use Maslosoft\Signals\Meta\DocumentPropertyMeta;
use Maslosoft\Signals\Meta\DocumentTypeMeta;
use Maslosoft\Signals\Meta\SignalsMeta;
use Maslosoft\Signals\Signal;
use ParseError;
use Psr\Log\LoggerInterface;
use ReflectionException;
use UnexpectedValueException;

/**
 * Addendum extractor
 * @codeCoverageIgnore
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
	 * Annotations matching patterns
	 * @var string[]
	 */
	private $patterns = [];
	private static $throw = false;
	private static $found = true;
	private static $file = '';

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
	 * Handler for class not found errors
	 *
	 * @internal This must be public, but should not be used anywhere else
	 * @param string $className
	 */
	public static function autoloadHandler($className)
	{
		// These are loaded in some other way...
		if ($className === 'PHP_Invoker')
		{
			return false;
		}
		if (stripos($className, 'phpunit') !== false)
		{
			return false;
		}
		if (!ClassChecker::exists($className))
		{
			if (self::$throw)
			{
				self::$found = false;
				throw new ClassNotFoundException("Class $className not found when processing " . self::$file);
			}
		}
		return false;
	}

	/**
	 * Get signals and slots data
	 * @return mixed
	 */
	public function getData()
	{
		$autoload = static::class . '::autoloadHandler';
		spl_autoload_register($autoload);
		(new FileWalker([], [$this, 'processFile'], $this->signal->paths, $this->signal->ignoreDirs))->walk();
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
	 * Get logger
	 * @return LoggerInterface
	 */
	public function getLogger()
	{
		return $this->signal->getLogger();
	}

	/**
	 * @param string $file
	 */
	public function processFile($file, $contents)
	{
		$this->getLogger()->debug("Processing `$file`");
		$file = realpath($file);

		self::$file = $file;

		$ignoreFile = sprintf('%s/.signalignore', dirname($file));

		if (file_exists($ignoreFile))
		{
			$this->getLogger()->notice("Skipping `$file` because of `$ignoreFile`" . PHP_EOL);
			return;
		}

		$this->paths[] = $file;
		// Remove initial `\` from namespace
		try
		{
			$annotated = AnnotationUtility::rawAnnotate($file);
		}
		catch (ClassNotFoundException $e)
		{
			$this->log($e, $file);
			return;
		}
		catch (ParseException $e)
		{
			$this->err($e, $file);
			return;
		}
		catch (UnexpectedValueException $e)
		{
			$this->err($e, $file);
			return;
		}
		catch (Exception $e)
		{
			$this->err($e, $file);
			return;
		}

		$namespace = preg_replace('~^\\\\+~', '', $annotated['namespace']);
		$className = $annotated['className'];


		// Use fully qualified name, class must autoload
		$fqn = $namespace . '\\' . $className;
		NameNormalizer::normalize($fqn);

		try
		{
			self::$throw = true;
			eval('$info = new ReflectionClass($fqn);');
			self::$throw = false;
			if (false === self::$found)
			{
				self::$found = true;
				throw new ClassNotFoundException("Class $className not found");
			}
		}
		catch (ParseError $e)
		{
			$this->err($e, $file);
			return;
		}
		catch (ClassNotFoundException $e)
		{
			$this->log($e, $file);
			return;
		}
		catch (ReflectionException $e)
		{
			$this->err($e, $file);
			return;
		}
		$isAnnotated = $info->implementsInterface(AnnotatedInterface::class);
		$hasSignals = $this->hasSignals($contents);
		$isAbstract = $info->isAbstract() || $info->isInterface();

		if ($isAnnotated)
		{
			$this->getLogger()->debug("Annotated: $info->name");
		}
		else
		{
			$this->getLogger()->debug("Not annotated: $info->name");
		}

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
		try
		{
			// Discard notices (might be the case when outdated cache?)
			$level = error_reporting();
			error_reporting(E_WARNING);
			$meta = SignalsMeta::create($fqn);
			error_reporting($level);
		}
		catch (ParseException $e)
		{
			$this->err($e, $file);
			return;
		}
		catch (ClassNotFoundException $e)
		{
			$this->log($e, $file);
			return;
		}
		catch (UnexpectedValueException $e)
		{
			$this->err($e, $file);
			return;
		}

		/* @var $typeMeta DocumentTypeMeta */
		$typeMeta = $meta->type();

		// Signals
		foreach ($typeMeta->signalFor as $slot)
		{
			$this->getLogger()->debug("Signal: $slot:$fqn");
			$this->data[Signal::Slots][$slot][$fqn] = true;
		}

		// Slots
		// For constructor injection
		foreach ($typeMeta->slotFor as $slot)
		{
			$key = implode('::', [$fqn, '__construct']) . '()';
			$this->getLogger()->debug("Slot: $slot:$fqn$key");
			$this->data[Signal::Signals][$slot][$fqn][$key] = true;
		}

		// For method injection
		foreach ($meta->methods() as $methodName => $method)
		{
			/* @var $method DocumentMethodMeta */
			foreach ($method->slotFor as $slot)
			{
				$key = implode('::', [$fqn, $methodName]) . '()';
				$this->getLogger()->debug("Slot: $slot:$fqn$key");
				$this->data[Signal::Signals][$slot][$fqn][$key] = sprintf('%s()', $methodName);
			}
		}

		// For property injection
		foreach ($meta->fields() as $fieldName => $field)
		{
			/* @var $field DocumentPropertyMeta */
			foreach ($field->slotFor as $slot)
			{
				$key = implode('::$', [$fqn, $fieldName]);
				$this->getLogger()->debug("Slot: $slot:$fqn$key");
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

	private function log(Exception $e, $file)
	{
		$msg = sprintf('Warning: %s while scanning file `%s`', $e->getMessage(), $file);
		$msg = $msg . PHP_EOL;
		$this->signal->getLogger()->warning($msg);
	}

	private function err(Exception $e, $file)
	{
		$msg = sprintf('Error: %s while scanning file `%s`', $e->getMessage(), $file);
		$msg = $msg . PHP_EOL;
		$this->signal->getLogger()->error($msg);
	}

}
