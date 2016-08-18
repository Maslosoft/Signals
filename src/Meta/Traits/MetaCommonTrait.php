<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\Signals\Meta;

/**
 * Common meta fields for all meta types (class, property, method)
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
trait MetaCommonTrait
{

	/**
	 *
	 * @var string[]
	 */
	public $slotFor = [];

	/**
	 *
	 * @var string[]
	 */
	public $signalFor = [];

}
