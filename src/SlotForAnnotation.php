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

use Maslosoft\Addendum\Helpers\ParamsExpander;
use Maslosoft\Addendum\Utilities\ClassChecker;
use Maslosoft\Signals\Helpers\NameNormalizer;
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

	public $value;

	public function init()
	{
		$data = ParamsExpander::expand($this, ['class']);
		$class = '';
		if (isset($data['class']))
		{
			$class = $data['class'];
		}
		// Log only, as it is designed as soft-fail
		if (empty($class) || !ClassChecker::exists($class))
		{
			(new Signal)->getLogger()->warning(sprintf('Class not found for @SlotFor on model `%s`', $this->getMeta()->type()->name));
			return;
		}
		NameNormalizer::normalize($class);
		$this->getEntity()->slotFor[] = $class;
	}

}
