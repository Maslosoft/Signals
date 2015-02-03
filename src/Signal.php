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

use Maslosoft\EmbeDi\EmbeDi;
use Maslosoft\Signals\Helpers\NameNormalizer;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Main signals components
 *
 * @author Piotr
 */
class Signal implements LoggerAwareInterface
{

	const slots = 'slots';
	const signals = 'signals';
	const ConfigFilename = 'signals-definition.php';

	public $configFilename = 'signals-definition.php';

	/**
	 * Runtime path
	 * @var string
	 */
	public $runtimePath = 'runtime';

	/**
	 * This aliases will be searched for SlotFor and SignalFor annotations
	 * TODO Autodetect based on composer autoload 
	 * @var string[]
	 */
	public $paths = [
		'application',
		'vendor',
		'maslosoft'
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
	private $log = null;

	/**
	 *
	 * @var EmbeDi
	 */
	private $_di = null;

	public function __construct()
	{
		$this->log = new NullLogger;
		$this->_di = new EmbeDi();
		$this->_di->configure($this);
	}

	public function init()
	{
		if (!$this->isInitialized)
		{
			$this->_init();
		}
		if(!$this->_di->isStored($this))
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
			$this->log->debug('No slots found for signal `{name}`, skipping', ['name' => $name]);
		}
		foreach (self::$_config[self::signals][$name] as $fqn => $injection)
		{
			// Skip
			if (false === $injection)
			{
				continue;
			}

			// Clone signal, as it might be modified by slot
			$cloned = clone $signal;

			// Constructor injection
			if (true === $injection)
			{
				new $fqn($cloned);
				$result[] = $cloned;
				continue;
			}

			// Othe type injection
			$slot = new $fqn;

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
		return $this->log;
	}

	/**
	 * Set logger
	 * @param LoggerInterface $logger
	 */
	public function setLogger(LoggerInterface $logger)
	{
		$this->log = $logger;
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
			$this->log->debug('Config file "{file}" does not exists, have you generated signals config file?', ['file' => $file]);
		}
	}

}
