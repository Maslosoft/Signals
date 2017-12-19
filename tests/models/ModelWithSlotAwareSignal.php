<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\SignalsTest\Models;

use Maslosoft\Addendum\Interfaces\AnnotatedInterface;
use Maslosoft\SignalsTest\Signals\SlotAwareSignal;

/**
 * ModelWithSlotAwareSignal
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class ModelWithSlotAwareSignal implements AnnotatedInterface
{

	/**
	 * @SlotFor(SlotAwareSignal)
	 * @param SlotAwareSignal $signal
	 */
	public function reactOn(SlotAwareSignal $signal)
	{
		
	}

}
