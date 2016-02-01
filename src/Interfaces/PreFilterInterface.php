<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\Signals\Interfaces;

/**
 * Implement this interface to filter out signals before instantiation.
 *
 * Pre filters will prevent instantiating a slot, as well as appearing it in results of emit.
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
interface PreFilterInterface extends FilterInterface
{

	public function filter($className, SignalInterface $signal);
}
