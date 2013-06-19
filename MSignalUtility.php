<?php

/**
 * Signals utility class
 *
 * @author Piotr
 */
class MSignalUtility extends CComponent
{
	public function generate()
	{
		
	}

	public function getAliasOfPath($path = __DIR__)
	{
		$path = str_replace(Yii::app()->basePath, '', $path);
		$path = sprintf('application%s', $path);
		$path = str_replace('\\', '/', $path);
		$path = str_replace('/', '.', $path);

		return $path . '.' . __CLASS__;
	}
}