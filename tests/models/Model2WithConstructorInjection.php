<?php

/**
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\SignalsTest\Models;

use Maslosoft\Addendum\Interfaces\AnnotatedInterface;
use Maslosoft\SignalsTest\Signals\ConstructorInjected;

/**
 * ModelWithConstructorInjection
 * @SlotFor(Maslosoft\SignalsTest\Signals\ConstructorInjected)
 * @see ConstructorInjected
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class Model2WithConstructorInjection implements AnnotatedInterface
{

	public function __construct(ConstructorInjected $signal)
	{
		$signal->emited = true;
	}

}
