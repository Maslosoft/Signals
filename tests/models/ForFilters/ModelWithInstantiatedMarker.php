<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\SignalsTest\Models\ForFilters;

use Maslosoft\Addendum\Interfaces\AnnotatedInterface;
use Maslosoft\SignalsTest\Signals\ForFilters\MethodInjectedPost;
use Maslosoft\SignalsTest\Signals\ForFilters\MethodInjectedPre;

/**
 * ModelWithInstantiatedMarker
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class ModelWithInstantiatedMarker implements AnnotatedInterface
{

	public static $instantiated = false;
	public static $injected = false;

	public function __construct()
	{
		self::$instantiated = true;
	}

	/**
	 * @SlotFor(MethodInjectedPre)
	 * @param MethodInjectedPre $signal
	 */
	public function reactOn(MethodInjectedPre $signal)
	{
		self::$injected = true;
	}

	/**
	 * @SlotFor(MethodInjectedPost)
	 * @param MethodInjectedPost $signal
	 */
	public function reactOnSecon(MethodInjectedPost $signal)
	{
		self::$injected = true;
	}

}
