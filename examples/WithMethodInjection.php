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
use Maslosoft\SignalsExamples\Signals\MethodInjected;

/**
 * Model with method injection
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class WithMethodInjection implements AnnotatedInterface
{

	/**
	 * @SlotFor(MethodInjected)
	 * @param MethodInjected $signal
	 */
	public function reactOn(MethodInjected $signal)
	{
		$signal->emitted = true;
	}

}
