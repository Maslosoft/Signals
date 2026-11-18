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

declare(strict_types=1);

namespace Maslosoft\Signals\Builder;

use Maslosoft\Signals\Interfaces\SignalInterface;
use Maslosoft\Signals\Interfaces\SlotInterface;
use Exception;
use function implode;
use function is_a;
use Maslosoft\Addendum\Exceptions\NoClassInFileException;
use Maslosoft\Addendum\Exceptions\ParseException;
use Maslosoft\Addendum\Interfaces\AnnotatedInterface;
use Maslosoft\Addendum\Builder\DocComment;
use Maslosoft\Addendum\Utilities\FileWalker;
use Maslosoft\Addendum\Utilities\NameNormalizer;
use Maslosoft\Signals\Exceptions\ClassNotFoundException;
use Maslosoft\Signals\Helpers\DataSorter;
use Maslosoft\Signals\Interfaces\ExtractorInterface;
use Maslosoft\Signals\Interfaces\PathsAwareInterface;
use Maslosoft\Signals\Meta\DocumentTypeMeta;
use Maslosoft\Signals\Meta\SignalsMeta;
use Maslosoft\Signals\Signal;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use function sprintf;
use UnexpectedValueException;
use LogicException;
use Throwable;

/**
 * Addendum extractor
 * @codeCoverageIgnore
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class Addendum implements ExtractorInterface, PathsAwareInterface
{

	// Data keys for annotations extraction
	public const SlotFor = 'SlotFor';
	public const SignalFor = 'SignalFor';
	// Default annotation names
	public const SlotName = 'SlotFor';
	public const SignalName = 'SignalFor';

	/**
	 * Signal instance
	 * @var Signal
	 */
	private ?Signal $signal = null;

	/**
	 * Signals and slots data
	 * @var array
	 */
	private array $data = [
		Signal::Slots => [
		],
		Signal::Signals => [
		]
	];

	/**
	 * Scanned file paths
	 * @var string[]
	 */
	private array $paths = [];

	/**
	 * Annotations matching patterns
	 * @var string[]
	 */
	private array $patterns = [];

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
	 * @return array
	 */
	public function getData(): array
	{
		if ($this->signal === null)
		{
			throw new LogicException('Set the Signal instance before extracting definitions.');
		}
		$this->data = [Signal::Slots => [], Signal::Signals => []];
		$this->paths = [];
		$paths = [];
		foreach($this->signal->paths as $path)
		{
			$real = realpath($path);
			if(false === $real || !is_dir($real))
			{
				$this->signal->getLogger()->warning("Directory $path could not be resolved to absolute path");
				continue;
			}
			$paths[] = $real;
		}
		(new FileWalker([], [$this, 'processFile'], $paths, $this->signal->ignoreDirs))->walk();
		DataSorter::sort($this->data);
		return $this->data;
	}

	/**
	 * Get scanned paths. This is available only after getData call.
	 * @return string[]
	 */
	public function getPaths():array
	{
		return $this->paths;
	}

	/**
	 * Set signal instance
	 * @param Signal $signal
	 */
	public function setSignal(Signal $signal): void
	{
		$this->signal = $signal;
	}

	/**
	 * Get logger
	 * @return LoggerInterface
	 */
	public function getLogger(): LoggerInterface
	{
		if ($this->signal === null)
		{
			throw new LogicException('Set the Signal instance before extracting definitions.');
		}
		return $this->signal->getLogger();
	}

	/**
	 * @param string $file
	 * @param string $contents
	 */
	public function processFile(string $file, string $contents): void
	{
		$this->getLogger()->debug("Processing `$file`");
		$real = realpath($file);
		if ($real === false || !is_file($real))
		{
			$this->getLogger()->warning('Cannot scan missing file {file}', ['file' => $file]);
			return;
		}
		$file = $real;

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
			$annotated = (new DocComment())->forFile($file);
		}
		catch(NoClassInFileException $e)
		{
			$this->log($e, $file);
			return;
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
			$info = new ReflectionClass($fqn);
		}
		catch (Throwable $e)
		{
			$this->err($e, $file);
			return;
		}
		$isAnnotated = $info->implementsInterface(AnnotatedInterface::class);
		$hasSignals = $this->hasSignals($contents);
		$isAbstract = $info->isAbstract() || $info->isInterface() || $info->isTrait();

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
			$meta = SignalsMeta::create($fqn);
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
			if(is_a($slot, SignalInterface::class, true))
			{
				$this->getLogger()->error(sprintf('Slot `%s` is used as signal on `%s`', $slot, $fqn));
			}
			$this->getLogger()->debug("Signal: $slot:$fqn");
			$this->data[Signal::Slots][$slot][$fqn] = true;
		}

		// Slots
		// For constructor injection
		foreach ($typeMeta->slotFor as $slot)
		{
			if(is_a($slot, SlotInterface::class, true))
			{
				$this->getLogger()->error(sprintf('Signal `%s` is used as slot on `%s`', $slot, $fqn));
			}
			$key = implode('::', [$fqn, '__construct']) . '()';
			$this->getLogger()->debug("Slot: $slot:$fqn$key");
			$this->data[Signal::Signals][$slot][$fqn][$key] = true;
		}

		// For method injection
		foreach ($meta->methods() as $methodName => $method)
		{
			foreach ($method->slotFor as $slot)
			{
				$key = implode('::', [$fqn, $methodName]) . '()';
				if(is_a($slot, SlotInterface::class, true))
				{
					$this->getLogger()->error(sprintf('Signal `%s` is used as slot on `%s`', $slot, $key));
				}
				$this->getLogger()->debug("Slot: $slot:$fqn$key");
				$this->data[Signal::Signals][$slot][$fqn][$key] = sprintf('%s()', $methodName);
			}
		}

		// For property injection
		foreach ($meta->fields() as $fieldName => $field)
		{
			foreach ($field->slotFor as $slot)
			{
				$key = implode('::$', [$fqn, $fieldName]);
				if(is_a($slot, SlotInterface::class, true))
				{
					$this->getLogger()->error(sprintf('Signal `%s` is used as slot on `%s`', $slot, $key));
				}
				$this->getLogger()->debug("Slot: $slot:$fqn$key");
				$this->data[Signal::Signals][$slot][$fqn][$key] = sprintf('%s', $fieldName);
			}
		}
	}

	private function hasSignals(string $contents): bool
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

	private function log(Throwable $e, string $file): void
	{
		$msg = sprintf('Warning: %s while scanning file `%s`', $e->getMessage(), $file);
		$msg .= PHP_EOL;
		$this->signal->getLogger()->warning($msg);
	}

	private function err(Throwable $e, string $file): void
	{
		// Uncomment for debugging
		$msg = sprintf('Error: %s while scanning file `%s`', $e->getMessage(), $file);
		$msg = $msg . PHP_EOL;

		// Don't output errors on Maslosoft test models
		if(preg_match('~Maslosoft\\\\\w+Test~', $e->getMessage()))
		{
			$this->signal->getLogger()->debug($msg);
			return;
		}

		// Skip PHP_Invoker class, as it is possibly deprecated anyway
		if(strstr($e->getMessage(), 'PHP_Invoker'))
		{
			$this->signal->getLogger()->debug($msg);
			return;
		}
		$this->signal->getLogger()->error($msg);
	}

}
