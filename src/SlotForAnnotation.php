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
use Maslosoft\Signals\Helpers\ExceptionFormatter;
use Maslosoft\Signals\Meta\SignalsAnnotation;
use UnexpectedValueException;

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

		if (empty($class) || !ClassChecker::exists($class))
		{
			$msg = ExceptionFormatter::formatForAnnotation($this, $class);
			throw new UnexpectedValueException($msg);
		}
		NameNormalizer::normalize($class);
		$this->getEntity()->slotFor[] = $class;
	}

}
