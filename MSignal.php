<?php
Yii::import('ext.signals.*');
/**
 * Description of MSignal
 *
 * @author Piotr
 */
class MSignal extends CApplicationComponent
{

	const ConfigFilename = 'signals-definition.php';

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
	 * @param IMSignal $signal
	 * @return IMSignalSlot[] Slot container
	 */
	public function emit(IMSignal $signal)
	{
		$class = get_class($signal);
		$result = [];
//		var_dump(self::$_config);
//		exit;
		foreach(self::$_config[$class] as $alias)
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

}
