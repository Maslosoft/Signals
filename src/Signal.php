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

use Maslosoft\Addendum\Utilities\NameNormalizer;
use Maslosoft\Cli\Shared\ConfigReader;
use Maslosoft\EmbeDi\EmbeDi;
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

	const slots = 'slots';
	const signals = 'signals';

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

	public $configFilename = 'signals-definition.php';

	/**
	 * Runtime path.
	 * This is path where config from yml will be stored.
	 * Path is relative to project root.
	 * @var string
	 */
	public $runtimePath = 'runtime';

	/**
	 * Generated path.
	 * This is path, where signals definition will be stored.
	 * Path is relative to project root.
	 * @var string
	 */
	public $generatedPath = 'generated';

	/**
	 * This aliases will be searched for SlotFor and SignalFor annotations
	 * TODO Autodetect based on composer autoload
	 * @var string[]
	 */
	public $paths = [
		'vendor',
	];

	/**
	 * Whenever component is initialized
	 * @var bool
	 */
	public $isInitialized = false;

	/**
	 * Configuration of signals and slots
	 * @var string[][]
	 */
	private static $_config = [];

	/**
	 * Logger
	 * @var LoggerInterface
	 */
	private $_log = null;

	/**
	 *
	 * @var EmbeDi
	 */
	private $_di = null;

	/**
	 * Version
	 * @var string
	 */
	private $_version = null;

	public function __construct($configName = self::ConfigName)
	{
		$this->_log = new NullLogger;

		/**
		 * TODO This should be made as embedi adapter, currently unsupported
		 */
		$config = new ConfigReader($configName);
		$this->_di = new EmbeDi();
		$this->_di->apply($config->toArray(), $this);
		$this->_di->configure($this);
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

	public function getVersion()
	{
		if (null === $this->_version)
		{
			$this->_version = require __DIR__ . '/version.php';
		}
		return $this->_version;
	}

	public function init()
	{
		if (!$this->isInitialized)
		{
			$this->_init();
		}
		if (!$this->_di->isStored($this))
		{
			$this->_di->store($this);
		}
	}

	/**
	 * Emit signal to inform slots
	 * @param object|string $signal
	 * @return object[]
	 */
	public function emit($signal)
	{
		$result = [];
		if (is_string($signal))
		{
			$signal = new $signal;
		}
		$name = get_class($signal);
		NameNormalizer::normalize($name);
		if (!isset(self::$_config[self::signals][$name]))
		{
			self::$_config[self::signals][$name] = [];
			$this->_log->debug('No slots found for signal `{name}`, skipping', ['name' => $name]);
		}
		foreach (self::$_config[self::signals][$name] as $fqn => $injections)
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
		$result = [];
		$name = get_class($slot);
		NameNormalizer::normalize($name);
		if (!isset(self::$_config[self::slots][$name]))
		{
			self::$_config[self::slots][$name] = [];
		}
		foreach ((array) self::$_config[self::slots][$name] as $fqn => $emit)
		{
			if (false === $emit)
			{
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
	 * @return LoggerInterface
	 */
	public function getLogger()
	{
		return $this->_log;
	}

	/**
	 * Set logger
	 * @param LoggerInterface $logger
	 */
	public function setLogger(LoggerInterface $logger)
	{
		$this->_log = $logger;
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
		$file = $this->runtimePath . '/' . $this->configFilename;
		if (file_exists($file))
		{
			self::$_config = require $file;
		}
		else
		{
			$this->_log->debug('Config file "{file}" does not exists, have you generated signals config file?', ['file' => $file]);
		}
	}

}
