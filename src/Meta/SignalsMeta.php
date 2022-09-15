<?php

/**
 * Wireless Cross-Component Communication
 *
 * This software package is licensed under `AGPL-3.0-only, proprietary` license[s].
 *
 * @package maslosoft/signals
 * @license AGPL-3.0-only, proprietary
 *
 * @copyright Copyright (c) Peter Maselkowski <pmaselkowski@gmail.com>
 * @link https://maslosoft.com/signals/
 */

namespace Maslosoft\Signals\Meta;

use Maslosoft\Addendum\Collections\Meta;
use Maslosoft\Addendum\Interfaces\AnnotatedInterface;
use Maslosoft\Addendum\Options\MetaOptions;
use Maslosoft\Signals\Options\SignalsMetaOptions;

/**
 * Signals metadata container class
 * @codeCoverageIgnore
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class SignalsMeta extends Meta
{

	/**
	 * Create instance of Metadata specifically designed for Signals
	 * @param string|object|AnnotatedInterface $model
	 * @param MetaOptions $options
	 * @return SignalsMeta
	 */
	public static function create($model, MetaOptions $options = null)
	{
		if (null === $options)
		{
			$options = new SignalsMetaOptions();
		}
		$meta = parent::create($model, $options);
		assert($meta instanceof SignalsMeta);
		return $meta;
	}

	/**
	 * Get field by name
	 * @param string $name
	 * @return DocumentPropertyMeta
	 */
	public function field($name)
	{
		$meta = parent::field($name);
		assert($meta instanceof DocumentPropertyMeta);
		return $meta;
	}

	/**
	 * Get document type meta
	 * @return DocumentTypeMeta
	 */
	public function type()
	{
		$meta = parent::type();
		assert($meta instanceof DocumentTypeMeta);
		return $meta;
	}

}
