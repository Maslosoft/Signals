<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\SignalsTest\Signals;

use Maslosoft\Signals\Interfaces\SignalInterface;

/**
 * Method Injected
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class MethodInjected implements SignalInterface
{

	public $emited = false;

}
