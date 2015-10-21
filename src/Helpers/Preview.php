<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\Signals\Helpers;

use Maslosoft\Signals\Builder\IO\Memory;
use Maslosoft\Signals\Signal;
use Maslosoft\Signals\Utility;

/**
 * Preview
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class Preview
{

	public function cli(Signal $signal)
	{
		$signal->setIO(new Memory());
		(new Utility($signal))->generate();
		$data = $signal->getIO()->read();
		return (new Renderer())->renderCli($data);
	}

}
