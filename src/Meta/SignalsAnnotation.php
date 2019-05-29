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
		$entity = parent::getEntity();
		assert(
			$entity instanceof DocumentTypeMeta ||
			$entity instanceof DocumentPropertyMeta ||
			$entity instanceof DocumentMethodMeta
		);
		return $entity;
	}

}
