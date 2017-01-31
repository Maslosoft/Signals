<?php

/**
 * This software package is licensed under `AGPL, Commercial` license[s].
 *
 * @package maslosoft/signals
 * @license AGPL, Commercial
 *
 * @copyright Copyright (c) Peter Maselkowski <pmaselkowski@gmail.com>
 * @link https://maslosoft.com/signals/
 */

namespace Maslosoft\Signals\Meta\Traits;

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
