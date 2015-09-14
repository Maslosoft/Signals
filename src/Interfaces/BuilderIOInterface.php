<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\Signals\Interfaces;

use Maslosoft\Signals\Signal;

/**
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
interface BuilderIOInterface
{

	public function setSignal(Signal $signal);

	public function write($data);

	public function read();
}
