<?php

namespace Maslosoft\Signals;

use CLogger;
use RuntimeException;
use Yii;

/**
 * Main signals components
 *
 * @author Piotr
 */
class Signal
{

	const slots = 'slots';
	const signals = 'signals';
	const ConfigFilename = 'signals-definition.php';

	public $configFilename = 'signals-definition.php';

	/**
	 * Path alias of where to store signals definitions
	 * TODO Change it to path
	 * @var string
	 */
	public $configAlias = 'autogen';

	/**
	 * This aliases will be searched for SlotFor and SignalFor annotations
	 * TODO Change it to paths
	 * @var string[]
	 */
	public $searchAliases = [
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

	public function init()
	{
		if (!$this->isInitialized)
		{
			$this->_init();
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
		if(is_string($signal))
		{
			$signal = new $signal;
		}
		$name = get_class($signal);
		if (!isset(self::$_config[self::signals][$name]))
		{
			self::$_config[self::signals][$name] = [];
			Yii::log(sprintf('No slots found for signal `%s`, skipping', $name), CLogger::LEVEL_INFO, 'Maslosoft.Signals');
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
			if(null === $interface)
			{
				$result[] = new $fqn;
				continue;
			}

			// Check if class implements interface
			if(isset(class_implements($fqn)[$interface]))
			{
				$result[] = new $fqn;
			}
		}
		return $result;
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
		$configPath = Yii::getPathOfAlias($this->configAlias);
		if (false === $configPath)
		{
			throw new RuntimeException(sprintf('Alias "%s" is invalid', $this->configAlias));
		}
		$file = $configPath . '/' . $this->configFilename;
		if(file_exists($file))
		{
			self::$_config = require $file;
		}
		else
		{
			Yii::log(sprintf('Config file "%s" does not exists, have you generated signals config file?', $file), CLogger::LEVEL_WARNING, 'Maslosoft.Signals');
		}
	}
}
