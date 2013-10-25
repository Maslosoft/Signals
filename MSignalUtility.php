<?php

/**
 * Signals utility class
 *
 * @author Piotr
 */
class MSignalUtility extends CComponent
{

	private $_data = [];

	public function generate()
	{
		$annotations = [
			'SlotFor',
			'SignalFor'
		];
		EAnnotationUtility::fileWalker($annotations, [$this, 'processFile']);
		echo '<pre>';
		var_dump($this->_data);
		echo '</pre>';
	}

	/**
	 * TODO Slot should be annotated at method level and store this method name in definiution file
	 * @param type $file
	 */
	public function processFile($file)
	{
		$value = EAnnotationUtility::rawAnnotate($file)['class'];
		$for = [
			'slots' => 'SignalFor',
			'signals' => 'SlotFor'
		];
		foreach ($for as $src => $annotation)
		{
			if (!isset($value[$annotation]))
			{
				continue;
			}
			$val = $this->_getValuesFor($value[$annotation]);
			foreach ($val as $slot)
			{
				$this->_data[$src][$slot][] = MSignalUtility::getAliasOfPath($file);
			}
		}
		echo '<pre>';
//		var_dump();
		echo '----------------' . "\n";
		var_dump($val);
		echo '</pre>';
	}

	private function _getValuesFor($src)
	{
		$value = [];
		foreach ($src as $val)
		{
			$val = $val['value'];
			if (is_array($val))
			{
				$value = array_merge($value, $val);
			}
			elseif (is_string($val))
			{
				$value[] = $val;
			}
		}
		return array_values(array_unique($value));
	}

	public static function getAliasOfPath($path = __DIR__)
	{
		$path = str_replace(Yii::app()->basePath, '', $path);
		$path = sprintf('application%s', $path);
		$path = str_replace('\\', '/', $path);
		$path = str_replace('/', '.', $path);
		$path = preg_replace('~\.php$~', '', $path);
		return $path;
	}

}
