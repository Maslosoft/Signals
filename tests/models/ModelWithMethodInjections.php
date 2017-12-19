<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\SignalsTest\Models;

use Maslosoft\Addendum\Interfaces\AnnotatedInterface;
use Maslosoft\SignalsTest\Signals\MethodInjected3;

/**
 * ModelWithMethodInjection
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class ModelWithMethodInjections implements AnnotatedInterface
{

	/**
	 * @SlotFor(MethodInjected3)
	 * @param MethodInjected3 $signal
	 */
	public function reactOn(MethodInjected3 $signal)
	{
		$signal->emited = true;
	}

	/**
	 * @SlotFor(MethodInjected3)
	 * @param MethodInjected3 $signal
	 */
	public function reactOnSameSignal(MethodInjected3 $signal)
	{
		$signal->emited = true;
	}

	/**
	 * @SlotFor(MethodInjected3)
	 * @param MethodInjected3 $signal
	 */
	public function reactOnSameSignal2(MethodInjected3 $signal)
	{
		$signal->emited = true;
	}

}
