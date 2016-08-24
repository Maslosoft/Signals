<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
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
	 * @return static
	 */
	public static function create($model, MetaOptions $options = null)
	{
		if (null === $options)
		{
			$options = new SignalsMetaOptions();
		}
		return parent::create($model, $options);
	}

	/**
	 * Get field by name
	 * @param string $name
	 * @return DocumentPropertyMeta
	 */
	public function field($name)
	{
		return parent::field($name);
	}

	/**
	 * Get document type meta
	 * @return DocumentTypeMeta
	 */
	public function type()
	{
		return parent::type();
	}

}
