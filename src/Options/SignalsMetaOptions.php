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

namespace Maslosoft\Signals\Options;

use Maslosoft\Addendum\Options\MetaOptions;
use Maslosoft\Signals\Meta\DocumentMethodMeta;
use Maslosoft\Signals\Meta\DocumentPropertyMeta;
use Maslosoft\Signals\Meta\DocumentTypeMeta;
use Maslosoft\Signals\SlotForAnnotation;

/**
 * MetaOptions
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class SignalsMetaOptions extends MetaOptions
{

	/**
	 * Meta container class name for type (class)
	 * @var string
	 */
	public $typeClass = DocumentTypeMeta::class;

	/**
	 * Meta container class name for method
	 * @var string
	 */
	public $methodClass = DocumentMethodMeta::class;

	/**
	 * Meta container class name for property
	 * @var string
	 */
	public $propertyClass = DocumentPropertyMeta::class;

	/**
	 * Namespaces for annotations
	 * @var string[]
	 */
	public $namespaces = [
		SlotForAnnotation::Ns
	];

}
