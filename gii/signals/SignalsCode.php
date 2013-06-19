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
		$annotations = [
			 'SlotFor',
			 'SignalFor'
		];
//		EAnnotationUtility::fileWalker($annotations, [$this, 'processFile']);
		Yii::app()->addendum;

		$file = __DIR__ . '/../../example/MExampleNameSignal.php';
		$this->processFile($file);
//		$string = file_get_contents($file);

		var_dump($value);

		$path = Yii::getPathOfAlias('autogen') . '/' . MSignal::ConfigFilename;
		$code = "<?\n";
		$code .= "return ";
		$code .= var_export([], true);
		$code .= ";";

		$this->files[] = new CCodeFile($path, $code);
	}

	public function processFile($file)
	{
		$value = EAnnotationUtility::rawAnnotate($file)['class'];


		var_dump($value);
	}

	private function _getValuesFor($key)
	{
		$value = [];
		if (isset($value[$key]))
		{
			foreach ($value[$key] as $val)
			{
				if (is_array($val))
				{
					$value = array_merge($value, $val);
				}
				elseif (is_string($val))
				{
					$value[] = $val;
				}
			}
		}
		return array_keys(array_unique($value));
	}

}