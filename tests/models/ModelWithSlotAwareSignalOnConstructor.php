<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\SignalsTest\Models;

use Maslosoft\Addendum\Interfaces\AnnotatedInterface;
use Maslosoft\SignalsTest\Signals\SlotAwareSignalForContructor;

/**
 * ModelWithSlotAwareSignal
 *
 * @SlotFor(SlotAwareSignalForContructor)
 * @see SlotAwareSignalForContructor
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class ModelWithSlotAwareSignalOnConstructor implements AnnotatedInterface
{
	
}
