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
		$path = Yii::getPathOfAlias('autogen');
		if (!Yii::app()->addendum)
		{
			$this->addError($attribute, 'Yii Signals requires Yii Addendum extension');
		}
		if (false === $path)
		{
			$this->addError($attribute, 'Path alias "autogen" is not defined');
		}
		if (!is_writable($path))
		{
			$this->addError($attribute, sprintf('Path alias "autogen" (%s) is not writable', realpath($path)));
		}
	}

	/**
	 * Generate code
	 * 
	 */
	public function prepare()
	{
		

//		$file = __DIR__ . '/../../example/MExampleNameSignal.php';
//		$this->processFile($file);
//		$string = file_get_contents($file);

//		var_dump($value);
		$data = (new MSignalUtility)->generate();
		$path = Yii::getPathOfAlias('autogen') . '/' . MSignal::ConfigFilename;
		$code = "<?\n";
		$code .= "return ";
		$code .= var_export($data, true);
		$code .= ";";

		$this->files[] = new CCodeFile($path, $code);
	}

	
	
	

}