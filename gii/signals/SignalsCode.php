<?php

Yii::import('ext.signals.MSignal');

/**
 * MSignalsCode
 *
 * @author Piotr
 */
class SignalsCode extends CCodeModel
{

	public $autogenAlias = 'autogen';

	public function rules()
	{
		return array_merge(parent::rules(), [
			 ['autogenAlias', 'checkAutogenAlias']
		]);
	}

	public function checkAutogenAlias($attribute, $params)
	{
		$path = Yii::getPathOfAlias('autogen');
		if(false === $path)
		{
			$this->addError($attribute, 'Path alias "autogen" is not defined');
		}
		if(!is_writable($path))
		{
			$this->addError($attribute, sprintf('Path alias "autogen" (%s) is not writable', realpath($path)));
		}
	}

	public function prepare()
	{
		$path = Yii::getPathOfAlias('autogen') . '/' . MSignal::ConfigFilename;
		$code = "<?\n";
		$code .= "return ";
		$code .= var_export([], true);
		$code .= ";";

		$this->files[] = new CCodeFile($path, $code);
	}

}