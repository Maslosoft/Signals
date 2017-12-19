<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\SignalsTest\Models;

use Maslosoft\Addendum\Interfaces\AnnotatedInterface;
use Maslosoft\SignalsTest\Signals\PropertyInjected;
use Maslosoft\SignalsTest\Signals\PropertyInjected2;

/**
 * ModelWithPropertyInjection
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class ModelWithPropertyInjection implements AnnotatedInterface
{

	/**
	 * @SlotFor(PropertyInjected)
	 * @var PropertyInjected
	 */
	public $signal = null;

	/**
	 * @var PropertyInjected
	 */
	private $_signal = null;

	public function __construct()
	{
		unset($this->signal);
	}

	public function __set($name, $value)
	{
		assert($value instanceof PropertyInjected || $value instanceof PropertyInjected2);
		$value->emited = true;
		$this->{"_$name"} = $value;
	}

}
