<?php

namespace Maslosoft\Signals;

use CApplicationComponent;
use CException;
use CLogger;
use RuntimeException;
use Yii;

/**
 * Main signals components
 *
 * @author Piotr
 */
class Signal extends CApplicationComponent
{

	const slots = 'slots';
	const signals = 'signals';
	const ConfigFilename = 'signals-definition.php';

	public $configFilename = 'signals-definition.php';

	/**
	 * Path alias of where to store signals definios
	 * @var string
	 */
	public $configAlias = 'autogen';

	/**
	 * This aliases will be searched for SlotFor and SignalFor annotations
	 * @var string[]
	 */
	public $searchAliases = [
		'application',
		'vendor',
		'maslosoft'
	];

	/**
	 * 
	 */
	public $containerClass = 'Maslosoft\Signals\Container';

	/**
	 * Access control callback.
	 * Callback signature:
	 * <code><pre>public function(string $role, User $user)</pre></code>
	 * @var callback
	 */
	public $accessCallback = [];
	private static $_config = [];

	public function init()
	{
		if (!$this->isInitialized)
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
				Yii::log(sprintf('Config file "%s" does not exists, have you generated signals config file?', $file));
			}
		}
		parent::init();
	}

	/**
	 * Emit signal to inform slots
	 * @param object $signal
	 * @return object[]
	 */
	public function emit($signal)
	{
		$result = [];
		$name = get_class($signal);
		if (!isset(self::$_config[self::signals][$name]))
		{
			self::$_config[self::signals][$name] = [];
		}
		foreach (self::$_config[self::signals][$name] as $alias => $injection)
		{
			// Skip
			if (false === $injection)
			{
				continue;
			}
			try
			{
				Yii::import($alias);
			}
			catch (CException $exc)
			{
				Yii::log(sprintf("Slot %s for signal %s not found", $alias, $name), CLogger::LEVEL_ERROR);
				continue;
			}
			// Constructor injection
			if (true === $injection)
			{
				$result[] = Yii::createComponent($alias, $signal);
				continue;
			}

			$slot = Yii::createComponent($alias);
			if (strstr($injection, '()'))
			{
				// Method injection
				$methodName = str_replace('()', '', $injection);
				$slot->$methodName($signal);
			}
			else
			{
				// field injection
				$slot->$injection = $signal;
			}
			$result[] = $slot;
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
		foreach ((array) self::$_config[self::slots][$name] as $alias => $emit)
		{
			if (false === $emit)
			{
				continue;
			}
			try
			{
				Yii::import($alias);
			}
			catch (CException $exc)
			{
				Yii::log(sprintf("Signal %s for slot %s not found", $alias, $name), CLogger::LEVEL_ERROR);
				continue;
			}
			if(null === $interface)
			{
				$component = Yii::createComponent($alias);
				$result[] = $component;
				continue;
			}

			// Check if class implements interface
			if(strstr($alias, '\\'))
			{
				$className = $alias;
			}
			else
			{
				$className = substr($alias, strrpos($alias, '.', -1) + 1);
			}
			if(isset(class_implements($className)[$interface]))
			{
				$component = Yii::createComponent($alias);
	//			if($component instanceof $interface)
				$result[] = $component;
			}
		}
		return $result;
	}

}
