<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\SignalsExamples;

use Maslosoft\Addendum\Interfaces\AnnotatedInterface;
use Maslosoft\SignalsExamples\Signals\ConstructorInjected;
use Maslosoft\SignalsExamples\Signals\MethodInjected;

/**
 * Model with method injection
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class WithMethodInjection implements AnnotatedInterface
{

	/**
	 * @SlotFor(MethodInjected)
	 * @param MethodInjected $signal
	 */
	public function reactOn(MethodInjected $signal)
	{
		$signal->emitted = true;
	}

}
