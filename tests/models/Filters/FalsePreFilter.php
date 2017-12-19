<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\SignalsTest\Models\Filters;

use Maslosoft\Signals\Interfaces\PreFilterInterface;
use Maslosoft\Signals\Interfaces\SignalInterface;

/**
 * This filter always returns false
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class FalsePreFilter implements PreFilterInterface
{

	public function filter($className, $signal)
	{
		return false;
	}

}
