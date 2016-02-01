<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\Signals\Interfaces;

/**
 * Implement this interface to create filter active after signals were instantiated.
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
interface PostFilterInterface
{

	public function filter(SignalInterface $signal);
}
