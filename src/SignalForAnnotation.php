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

namespace Maslosoft\Signals;

use Maslosoft\Addendum\Helpers\ParamsExpander;
use Maslosoft\Addendum\Utilities\ClassChecker;
use Maslosoft\Addendum\Utilities\NameNormalizer;
use Maslosoft\Signals\Meta\SignalsAnnotation;

/**
 * SignalForAnnotation
 * @template SignalFor(${SlotClass})
 * @codeCoverageIgnore
 * @author Piotr
 */
class SignalForAnnotation extends SignalsAnnotation
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
			(new Signal)->getLogger()->warning(sprintf('Class not found for SignalFor annotation on model `%s`', $this->getMeta()->type()->name));
			return;
		}
		NameNormalizer::normalize($class);
		$this->getEntity()->signalFor[] = $class;
	}

}
