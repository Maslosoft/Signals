<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\Signals\Builder\IO;

use Maslosoft\Signals\Interfaces\BuilderIOInterface;
use Maslosoft\Signals\Signal;

/**
 * Memory IO. It does not persist configuration.
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class Memory implements BuilderIOInterface
{

	private $data = null;

	public function read()
	{
		return $this->data;
	}

	public function setSignal(Signal $signal)
	{

	}

	public function write($data)
	{
		$this->data = $data;
		return true;
	}

}
