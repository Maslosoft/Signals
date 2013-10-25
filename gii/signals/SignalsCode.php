<?php

Yii::import('ext.signals.MSignal');
Yii::import('ext.signals.MSignalUtility');

/**
 * MSignalsCode
 *
 * @author Piotr
 */
class SignalsCode extends CCodeModel
{

	public $autogenAlias = 'autogen';
	private $_data = [];

	public function rules()
	{
		return array_merge(parent::rules(), [
			 ['autogenAlias', 'checkAutogenAlias']
		]);
	}

	public function checkAutogenAlias($attribute, $params)
	{
		$path = Yii::getPathOfAlias(Yii::app()->signal->configAlias);
		if (!Yii::app()->addendum)
		{
			$this->addError($attribute, 'Yii Signals requires Yii Addendum extension');
		}
		if (false === $path)
		{
			$this->addError($attribute, sprintf('Path alias "%s" does not exists, configure "configAlias" property of signal extension', Yii::app()->signal->configAlias));
		}
		if (!is_writable($path))
		{
			$this->addError($attribute, sprintf('Path alias "%s" (%s) is not writable', Yii::app()->signal->configAlias, realpath($path)));
		}
	}

	/**
	 * Generate code
	 * 
	 */
	public function prepare()
	{
		$data = (new MSignalUtility)->generate();
		$path = Yii::getPathOfAlias('autogen') . '/' . MSignal::ConfigFilename;
		$code = "<?\n";
		$code .= "return ";
		$code .= var_export($data, true);
		$code .= ";";

		$this->files[] = new CCodeFile($path, $code);
	}

	
	
	

}