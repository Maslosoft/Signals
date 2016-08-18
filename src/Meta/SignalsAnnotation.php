<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\Signals\Meta;

use Maslosoft\Addendum\Collections\MetaAnnotation;

/**
 * SignalsAnnotation
 *
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
