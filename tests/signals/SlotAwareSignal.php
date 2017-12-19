<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\SignalsTest\Signals;

use Maslosoft\Signals\Interfaces\SignalInterface;
use Maslosoft\Signals\Interfaces\SlotAwareInterface;

/**
 * SlotAwareSignal
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class SlotAwareSignal implements SignalInterface, SlotAwareInterface
{

	public $slotClass = '';

	public function setSlot($slot)
	{
		$this->slotClass = get_class($slot);
	}

}
