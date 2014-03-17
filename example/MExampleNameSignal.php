<?php

use Maslosoft\Signals\ISignal;

/**
 * @SignalFor('DashboardController')
 * @SignalFor({'OtherSlot', 'AndThis'})
 * @author Piotr
 */
class MExampleNameSignal extends CComponent implements ISignal, DashboardWidget
{

	public $name = 'foo';

	public function run()
	{
		return __CLASS__;
	}

	public function getName()
	{
		return 'Test';
	}

}
