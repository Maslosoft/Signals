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

namespace Maslosoft\Signals;

use Maslosoft\Addendum\Helpers\ParamsExpander;
use Maslosoft\Addendum\Utilities\ClassChecker;
use Maslosoft\Addendum\Utilities\NameNormalizer;
use Maslosoft\Signals\Helpers\ExceptionFormatter;
use Maslosoft\Signals\Meta\SignalsAnnotation;
use UnexpectedValueException;

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
		if (empty($class) || !ClassChecker::exists($class))
		{
			$msg = ExceptionFormatter::formatForAnnotation($this, $class);
			throw new UnexpectedValueException($msg);
		}
		NameNormalizer::normalize($class);
		$this->getEntity()->signalFor[] = $class;
	}

}
