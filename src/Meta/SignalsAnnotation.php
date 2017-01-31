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

namespace Maslosoft\Signals\Meta;

use Maslosoft\Addendum\Collections\MetaAnnotation;

/**
 * SignalsAnnotation
 * @codeCoverageIgnore
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
abstract class SignalsAnnotation extends MetaAnnotation
{

	/**
	 * Get entity
	 * @return DocumentTypeMeta|DocumentPropertyMeta|DocumentMethodMeta
	 */
	public function getEntity()
	{
		return parent::getEntity();
	}

}
