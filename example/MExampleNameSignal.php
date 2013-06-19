<?php

/**
 * @SignalFor('SomeSlot')
 * @SignalFor({'OtherSlot', 'AndThis'})
 * @author Piotr
 */
class MExampleNameSignal extends CComponent implements IMSignal
{
	public $name = 'foo';
}