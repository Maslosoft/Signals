<?php

namespace Maslosoft\Signals\Gii;

use CCodeFile;
use CCodeModel;
use Maslosoft\Signals\Utility;
use Yii;

Yii::import('gii.*');

/**
 * Signals code
 *
 * @author Piotr
 */
class SignalsCode extends CCodeModel
{

	private $_data = [];

	public function rules()
	{
		return array_merge(parent::rules(), [
			['configAlias', 'checkConfigAlias']
		]);
	}

	public function getConfigAlias()
	{
		return Yii::app()->signal->configAlias;
	}

	public function setConfigAlias($value)
	{
		// Do nothing
	}

	public function checkconfigAlias($attribute, $params)
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
		$data = (new Utility)->generate();
		$path = Yii::getPathOfAlias(Yii::app()->signal->configAlias) . '/' . Yii::app()->signal->configFilename;
		$code = "<?php\n";
		$code .= "return ";
		$code .= var_export($data, true);
		$code .= ";";

		$this->files[] = new CCodeFile($path, $code);
	}

	/**
	 * Saves the generated code into files.
	 */
	public function save()
	{
		$result = true;
		foreach ($this->files as $file)
		{
			// Dont ask anything, just overwrite
			$result = $file->save() && $result;
		}
		return $result;
	}

}
