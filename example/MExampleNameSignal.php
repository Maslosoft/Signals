<?php

use Maslosoft\Signals\ISignal;

/**
 * @SignalFor('SomeDashboardController')
 * @SignalFor({'OtherSlot', 'AndThis'})
 * @author Piotr
 */
class MExampleNameSignal extends CComponent implements ISignal
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
