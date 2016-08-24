<?php

/**
 * This software package is licensed under `AGPL, Commercial` license[s].
 *
 * @package maslosoft/signals
 * @license AGPL, Commercial
 *
 * @copyright Copyright (c) Peter Maselkowski <pmaselkowski@gmail.com>
 *
 */

namespace Maslosoft\Signals\Builder\IO;

use Maslosoft\Cli\Shared\Helpers\PhpExporter;
use Maslosoft\Signals\Interfaces\BuilderIOInterface;
use Maslosoft\Signals\Signal;

/**
 * PhpFileOutput
 * @codeCoverageIgnore
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class PhpFile implements BuilderIOInterface
{

	/**
	 * Generated path.
	 * This is path, where signals definition will be stored.
	 * Path is relative to project root.
	 * @var string
	 */
	public $generatedPath = 'generated';

	/**
	 * File name for file containing signals definitions.
	 * @var string
	 */
	public $configFilename = 'signals-definition.php';

	/**
	 * Signal instance
	 * @var Signal
	 */
	private $signal = null;

	public function read()
	{
		$file = $this->generatedPath . '/' . $this->configFilename;
		if (file_exists($file))
		{
			return (array) require $file;
		}
		else
		{
			$this->signal->getLogger()->debug('Config file "{file}" does not exists, have you generated signals config file?', ['file' => $file]);
		}
	}

	public function setSignal(Signal $signal)
	{
		$this->signal = $signal;
		return $this;
	}

	public function write($data)
	{
		$path = sprintf("%s/%s", $this->generatedPath, $this->configFilename);

		// Use dirname here in case configFilename contains dir
		$dir = dirname($path);
		if (!is_dir($dir))
		{
			mkdir($dir, 0777, true);
		}
		return file_put_contents($path, PhpExporter::export($data, 'Auto generated, any changes will be lost'));
	}

}
