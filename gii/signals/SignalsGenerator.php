<?php

/**
 * Code generator class for signals
 *
 * @author Piotr
 */
class SignalsGenerator extends CCodeGenerator
{

	public $codeModel = 'Maslosoft\Signals\Gii\SignalsCode';

	/**
	 * Prepares the code model.
	 */
	protected function prepare()
	{
		if($this->codeModel===null)
			throw new CException(get_class($this).'.codeModel property must be specified.');
		$modelClass=Yii::import($this->codeModel,true);
		$model=new $modelClass;
		$model->loadStickyAttributes();
		// NOTE: This is added because usual generator was not handling namespaces properly
		$postVar = str_replace('\\', '_', $modelClass);
		if(isset($_POST[$postVar]))
		{
			$model->attributes=$_POST[$postVar];
			$model->status=CCodeModel::STATUS_PREVIEW;
			if($model->validate())
			{
				$model->saveStickyAttributes();
				$model->prepare();
			}
		}
		return $model;
	}
}
