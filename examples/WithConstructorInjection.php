<?php

/**
 * This software package is licensed under `AGPL-3.0-only, proprietary` license[s].
 *
 * @package maslosoft/signals
 * @license AGPL-3.0-only, proprietary
 *
 * @copyright Copyright (c) Peter Maselkowski <pmaselkowski@gmail.com>
 * @link https://maslosoft.com/signals/
 */

namespace Maslosoft\SignalsExamples;

use Maslosoft\Addendum\Interfaces\AnnotatedInterface;
use Maslosoft\SignalsExamples\Signals\ConstructorInjected;

/**
 * Model with constructor injection
 * @SlotFor(ConstructorInjected)
 *
 * @see ConstructorInjected
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class WithConstructorInjection implements AnnotatedInterface
{

	public function __construct(ConstructorInjected $signal = null)
	{
		if(!empty($signal))
		{
			$signal->emitted = true;
		}
	}

}
