<?php

use Maslosoft\Signals\ISignal;

/**
 * MExampleSlot
 * @Label('Say hello to signal')
 * @Description('This slot is saying helo to any signal')
 * @SlotFor('MExampleSignal')
 * @\Allowed('ua.user.allowed')
 * @author Piotr
 */
class MExampleSlot extends CComponent implements IAnnotated
{

	private $_signal = null;

	public function result()
	{
		var_dump(Yii::app()->basePath);

		$path = str_replace(Yii::app()->basePath, '', __DIR__);
		$path = sprintf('application%s', $path);
		$path = str_replace('\\', '/', $path);
		$path = str_replace('/', '.', $path);

		return $path . '.' . __CLASS__;
		return sprintf('Hello %s', $this->_signal->name);
	}

	public function setSignal(ISignal $signal)
	{
		$this->_signal = $signal;
	}

}
