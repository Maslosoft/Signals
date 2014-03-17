<?php

namespace Maslosoft\Signals;

use CApplicationComponent;
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

	/**
	 * Path alias of where to store signals definios
	 * @var string
	 */
	public $configAlias = 'autogen';

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
			$configPath = Yii::getPathOfAlias('autogen');
			if (false === $configPath)
			{
				throw new RuntimeException('Alias "autogen" is not defined');
			}
			$file = $configPath . '/' . self::ConfigFilename;
			if(file_exists($file))
			{
				self::$_config = require $file;
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
			return $result;
		}
		foreach (self::$_config[self::signals][$name] as $alias => $injection)
		{
			// Skip
			if (false === $injection)
			{
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
	 */
	public function gather($slot, $interface = null)
	{
		$result = [];
		$name = get_class($slot);
		foreach ((array)self::$_config[self::slots][$name] as $alias => $emit)
		{
			if (false === $emit)
			{
				continue;
			}
			if(null === $interface)
			{
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
			Yii::import($alias);
			if(class_implements($className)[$interface])
			{
				$component = Yii::createComponent($alias);
	//			if($component instanceof $interface)
				$result[] = $component;
			}
		}
		return $result;
	}

}
