<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\SignalsTest\Models\Filters;

use Maslosoft\Signals\Interfaces\PostFilterInterface;

/**
 * This filter always returns false
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class FalsePostFilter implements PostFilterInterface
{

	public function filter($signal)
	{
		return false;
	}

}
