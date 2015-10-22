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

namespace Maslosoft\Signals;

use Maslosoft\Addendum\Utilities\ClassChecker;
use Maslosoft\Addendum\Utilities\NameNormalizer;
use Maslosoft\Cli\Shared\ConfigReader;
use Maslosoft\EmbeDi\EmbeDi;
use Maslosoft\Signals\Builder\Addendum;
use Maslosoft\Signals\Builder\IO\PhpFile;
use Maslosoft\Signals\Interfaces\BuilderIOInterface;
use Maslosoft\Signals\Interfaces\ExtractorInterface;
use Maslosoft\Signals\Interfaces\SignalAwareInterface;
use Maslosoft\Signals\Interfaces\SlotAwareInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Main signals components
 *
 * @author Piotr
 * @property LoggerInterface $logger Logger, set this to log warnings, notices errors. This is shorthand for `get/setLogger`.
 */
class Signal implements LoggerAwareInterface
{

	const Slots = 'slots';
	const Signals = 'signals';

	/**
	 * Generated signals name.
	 * Name of this constant is confusing.
	 * @internal description
	 */
	const ConfigFilename = 'signals-definition.php';

	/**
	 * Config file name
	 */
	const ConfigName = "signals";

	/**
	 * Runtime path.
	 * This is path where config from yml will be stored.
	 * Path is relative to project root.
	 * @var string
	 */
	public $runtimePath = 'runtime';

	/**
	 * This aliases will be searched for SlotFor and SignalFor annotations
	 * TODO Autodetect based on composer autoload
	 * @var string[]
	 */
	public $paths = [
		'vendor',
	];

	/**
	 * Extractor configuration
	 * @var string|[]|object
	 */
	public $extractor = Addendum::class;

	/**
	 * Input/Output configuration
	 * @var string|[]|object
	 */
	public $io = PhpFile::class;

	/**
	 * Whenever component is initialized
	 * @var bool
	 */
	public $isInitialized = false;

	/**
	 * Configuration of signals and slots
	 * @var string[][]
	 */
	private static $config = [];

	/**
	 * Logger
	 * @var LoggerInterface
	 */
	private $logger = null;

	/**
	 *
	 * @var EmbeDi
	 */
	private $di = null;

	/**
	 * Version
	 * @var string
	 */
	private $version = null;

	public function __construct($configName = self::ConfigName)
	{
		$this->logger = new NullLogger;

		/**
		 * TODO This should be made as embedi adapter, currently unsupported
		 */
		$config = new ConfigReader($configName);
		$this->di = EmbeDi::fly();
		$this->di->apply($config->toArray(), $this);
		$this->di->configure($this);
	}

	/**
	 * Getter
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		return $this->{'get' . ucfirst($name)}();
	}

	/**
	 * Setter
	 * @param string $name
	 * @param mixed $value
	 * @return mixed
	 */
	public function __set($name, $value)
	{
		return $this->{'set' . ucfirst($name)}($value);
	}

	/**
	 * Get current signals version
	 *
	 * @codeCoverageIgnore
	 * @return string
	 */
	public function getVersion()
	{
		if (null === $this->version)
		{
			$this->version = require __DIR__ . '/version.php';
		}
		return $this->version;
	}

	public function init()
	{
		if (!$this->isInitialized)
		{
			$this->_init();
		}
		if (!$this->di->isStored($this))
		{
			$this->di->store($this);
		}
	}

	/**
	 * Emit signal to inform slots
	 * @param object|string $signal
	 * @return object[]
	 */
	public function emit($signal)
	{
		if (is_string($signal))
		{
			$signal = new $signal;
		}
		$name = get_class($signal);
		NameNormalizer::normalize($name);
		if (!isset(self::$config[self::Signals][$name]))
		{
			self::$config[self::Signals][$name] = [];
			$this->logger->debug('No slots found for signal `{name}`, skipping', ['name' => $name]);
		}
		$result = [];
		foreach (self::$config[self::Signals][$name] as $fqn => $injections)
		{
			// Skip
			if (false === $injections || count($injections) == 0)
			{
				continue;
			}

			// Clone signal, as it might be modified by slot
			foreach ($injections as $injection)
			{
				$cloned = clone $signal;



				// Constructor injection
				if (true === $injection)
				{
					$slot = new $fqn($cloned);

					// Slot aware call
					if ($cloned instanceof SlotAwareInterface)
					{
						$cloned->setSlot($slot);
					}
					$result[] = $cloned;
					continue;
				}

				// Check if class exists and log if doesn't
				if (!ClassChecker::exists($fqn))
				{
					$this->logger->debug(sprintf("Class `%s` not found while emiting signal `%s`", $fqn, get_class($signal)));
					continue;
				}

				// Othe type injection
				$slot = new $fqn;

				// Slot aware call
				if ($cloned instanceof SlotAwareInterface)
				{
					$cloned->setSlot($slot);
				}

				if (strstr($injection, '()'))
				{
					// Method injection
					$methodName = str_replace('()', '', $injection);
					$slot->$methodName($cloned);
				}
				else
				{
					// field injection
					$slot->$injection = $cloned;
				}
				$result[] = $cloned;
			}
		}
		return $result;
	}

	/**
	 * Call for signals from slot
	 * @param object $slot
	 * @param string $interface Interface, which must be implemented to get into slot
	 */
	public function gather($slot, $interface = null)
	{
		$name = get_class($slot);
		NameNormalizer::normalize($name);
		if (!isset(self::$config[self::Slots][$name]))
		{
			self::$config[self::Slots][$name] = [];
			$this->logger->debug('No signals found for slot `{name}`, skipping', ['name' => $name]);
		}
		$result = [];
		foreach ((array) self::$config[self::Slots][$name] as $fqn => $emit)
		{
			if (false === $emit)
			{
				continue;
			}
			// Check if class exists and log if doesn't
			if (!ClassChecker::exists($fqn))
			{
				$this->logger->debug(sprintf("Class `%s` not found while gathering slot `%s`", $fqn, get_class($slot)));
				continue;
			}
			if (null === $interface)
			{
				$result[] = new $fqn;
				continue;
			}

			// Check if class implements interface
			if (isset(class_implements($fqn)[$interface]))
			{
				$result[] = new $fqn;
			}
		}
		return $result;
	}

	/**
	 * Get logger
	 * @codeCoverageIgnore
	 * @return LoggerInterface
	 */
	public function getLogger()
	{
		return $this->logger;
	}

	/**
	 * Set logger
	 * @codeCoverageIgnore
	 * @param LoggerInterface $logger
	 */
	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * Get Input/Output adapter
	 * @codeCoverageIgnore
	 * @return BuilderIOInterface I/O Adapter
	 */
	public function getIO()
	{
		return $this->getConfigured('io');
	}

	/**
	 * Set Input/Output interface
	 * @codeCoverageIgnore
	 * @param BuilderIOInterface $io
	 * @return Signal
	 */
	public function setIO(BuilderIOInterface $io)
	{
		return $this->setConfigured($io, 'io');
	}

	/**
	 * @codeCoverageIgnore
	 * @return ExtractorInterface
	 */
	public function getExtractor()
	{
		return $this->getConfigured('extractor');
	}

	/**
	 * @codeCoverageIgnore
	 * @param ExtractorInterface $extractor
	 */
	public function setExtractor(ExtractorInterface $extractor)
	{
		$this->setConfigured($extractor, 'extractor');
	}

	/**
	 * Reloads signals cache and reinitializes component.
	 */
	public function resetCache()
	{
		$this->_init();
	}

	private function _init()
	{
		self::$config = $this->getIO()->read();
	}

	/**
	 * Get configured property
	 * @param string $property
	 * @return SignalAwareInterface
	 */
	private function getConfigured($property)
	{
		if (is_object($this->$property))
		{
			$object = $this->$property;
		}
		else
		{
			$object = $this->di->apply($this->$property);
		}
		if ($object instanceof SignalAwareInterface)
		{
			$object->setSignal($this);
		}
		return $object;
	}

	/**
	 * Set signal aware property
	 * @param SignalAwareInterface $object
	 * @param type $property
	 * @return Signal
	 */
	private function setConfigured(SignalAwareInterface $object, $property)
	{
		$object->setSignal($this);
		$this->$property = $object;
		return $this;
	}

}
