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
	 * @param MetaOptions|null                 $options
	 * @return static
	 */
	public static function create($model, ?MetaOptions $options = null): static
	{
		if (null === $options)
		{
			$options = new SignalsMetaOptions();
		}
		$meta = parent::create($model, $options);
		return $meta;
	}

	/**
	 * Get field by name
	 * @param string $name
	 * @return DocumentPropertyMeta
	 */
	public function field($name): DocumentPropertyMeta
	{
		$meta = parent::field($name);
		assert($meta instanceof DocumentPropertyMeta);
		return $meta;
	}

	/**
	 * Get document type meta
	 * @return DocumentTypeMeta
	 */
	public function type(): DocumentTypeMeta
	{
		$meta = parent::type();
		assert($meta instanceof DocumentTypeMeta);
		return $meta;
	}

	/** @return DocumentMethodMeta[] */
	public function methods(): array
	{
		$methods = [];
		foreach (parent::methods() as $name => $method)
		{
			if (!$method instanceof DocumentMethodMeta)
			{
				throw new \UnexpectedValueException('Signals metadata requires DocumentMethodMeta methods.');
			}
			$methods[$name] = $method;
		}
		return $methods;
	}

	/** @return DocumentPropertyMeta[] */
	public function fields(): array
	{
		$fields = [];
		foreach (parent::fields() as $name => $field)
		{
			if (!$field instanceof DocumentPropertyMeta)
			{
				throw new \UnexpectedValueException('Signals metadata requires DocumentPropertyMeta properties.');
			}
			$fields[$name] = $field;
		}
		return $fields;
	}

}
