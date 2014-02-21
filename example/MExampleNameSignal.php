<?php

use Maslosoft\Signals\ISignal;

/**
 * @SignalFor('SomeSlot')
 * @SignalFor({'OtherSlot', 'AndThis'})
 * @author Piotr
 */
class MExampleNameSignal extends CComponent implements ISignal
{

	public $name = 'foo';

}
