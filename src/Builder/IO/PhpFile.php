<?php

/**
 * Wireless Cross-Component Communication
 *
 * This software package is licensed under `AGPL-3.0-only, proprietary` license[s].
 *
 * @package maslosoft/signals
 * @license AGPL-3.0-only, proprietary
 *
 * @copyright Copyright (c) Peter Maselkowski <pmaselkowski@gmail.com>
 * @link https://maslosoft.com/signals/
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
	 * Generated path is directory, where signals definition will be stored.
	 * Path is relative to project root.
	 *
	 * **This directory must be writable by command line user**
	 *
	 * **This directory should be committed and distributed along with the project code**
	 *
	 * @var string
	 */
	public $generatedPath = 'generated';

	/**
	 * File name for file containing signals definitions, in most cases
	 * leaving default value is fine.
	 *
	 * @var string
	 */
	public $configFilename = 'signals-definition.php';

	/**
	 * Signal instance
	 * @var Signal
	 */
	private $signal = null;

	public function read(): array
	{
		$file = $this->generatedPath . '/' . $this->configFilename;
		if (file_exists($file))
		{
			return (array)require $file;
		}
		else
		{
			$this->signal->getLogger()->debug('Config file "{file}" does not exists, have you generated signals config file?', ['file' => $file]);
		}
		return [];
	}

	public function setSignal(Signal $signal)
	{
		$this->signal = $signal;
		return $this;
	}

	public function write($data): bool
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
