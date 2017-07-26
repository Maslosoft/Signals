<?php
/**
 * Created by PhpStorm.
 * User: peter
 * Date: 26.07.17
 * Time: 19:15
 */

namespace Maslosoft\SignalsExamples;
<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\SignalsExamples;

use Maslosoft\Addendum\Interfaces\AnnotatedInterface;
use Maslosoft\SignalsExamples\Signals\ConstructorInjected;

/**
 * Model with constructor injection
 * @SlotFor(ConstructorInjected)
 *
 * @see ConstructorInjected
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class WithConstructorInjection implements AnnotatedInterface
{

	public function __construct(ConstructorInjected $signal)
	{
		$signal->emited = true;
	}

}
