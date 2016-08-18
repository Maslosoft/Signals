<?php

/**
 * This software package is licensed under `AGPL, Commercial` license[s].
 *
 * @package maslosoft/signals
 * @license AGPL, Commercial
 *
 * @copyright Copyright (c) Peter Maselkowski <pmaselkowski@gmail.com>
 *
 */

namespace Maslosoft\Signals;

use Maslosoft\Signals\Meta\SignalsAnnotation;

/**
 * SlotForAnnotation
 * @template SlotFor(${SignalClass})
 * @codeCoverageIgnore
 * @author Piotr
 */
class SlotForAnnotation extends SignalsAnnotation
{

	const Ns = __NAMESPACE__;

	public function init()
	{
		
	}

}
