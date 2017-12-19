<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\SignalsTest\Models\Gather\ForFilters;

use Maslosoft\Addendum\Interfaces\AnnotatedInterface;
use Maslosoft\SignalsTest\Signals\Slots\GatherFilteredSlot;

/**
 * ModelWithInstantiatedMarker
 * @SignalFor(GatherFilteredSlot)
 * @see GatherFilteredSlot
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class GatherModelWithInstantiatedMarker implements AnnotatedInterface
{

	public static $instantiated = false;
	public static $injected = false;

	public function __construct()
	{
		self::$instantiated = true;
	}

}
