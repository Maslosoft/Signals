<?php
Yii::setPathOfAlias('yii-signals', __DIR__);
Yii::import('yii-signals.*');

/**
 * Description of MSignal
 *
 * @author Piotr
 */
class MSignal extends CApplicationComponent
{

	const ConfigFilename = 'signals-definition.php';

	/**
	 * Path alias of where to store signals definios
	 * @var string
	 */
	public $configAlias = 'autogen';

	/**
	 * 
	 */
	public $containerClass = 'ext.signals.MSignalContainer';

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
			self::$_config = require $configPath . '/' . self::ConfigFilename;
		}
		parent::init();
	}

	/**
	 * Emit signal and get results from connected slots
	 * @param IMSignal $signal
	 * @return IMSignalSlot[] Slot container
	 */
	public function emit(IMSignal $signal)
	{
		$class = get_class($signal);
		$result = [];
//		var_dump(self::$_config);
//		exit;
		foreach (self::$_config[$class] as $alias)
		{
			$slot = Yii::createComponent($alias);
			$slot->setSignal($signal);
			$container = new MSignalContainer();
			$container->result = $slot->result();
			$container->meta = EComponentMeta::create($slot);
			$result[] = $container;
		}
		return $result;
	}

	/**
	 * Call for signals from slot
	 * @param IMSignalSlot $slot
	 */
	public function collect(IMSignalSlot $slot)
	{

	}
}
