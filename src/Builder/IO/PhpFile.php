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

declare(strict_types=1);

namespace Maslosoft\Signals\Builder\IO;

use Maslosoft\Cli\Shared\Helpers\PhpExporter;
use InvalidArgumentException;
use RuntimeException;
use UnexpectedValueException;
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
	public string $generatedPath = 'generated';

	/**
	 * File name for file containing signals definitions, in most cases
	 * leaving default value is fine.
	 *
	 * @var string
	 */
	public string $configFilename = 'signals-definition.php';

	/**
	 * Signal instance
	 * @var Signal
	 */
	private ?Signal $signal = null;

	public function read(): array
	{
		$file = $this->generatedPath . '/' . $this->configFilename;
		if (!file_exists($file))
		{
			$this->signal?->getLogger()->debug('Config file "{file}" does not exist; generate the signals definitions first.', ['file' => $file]);
			return [];
		}
		$data = require $file;
		if (!is_array($data))
		{
			throw new UnexpectedValueException(sprintf('Signals definition file "%s" must return an array.', $file));
		}
		return $data;
	}

	public function setSignal(Signal $signal): static
	{
		$this->signal = $signal;
		return $this;
	}

	public function write($data): bool
	{
		if (!is_array($data))
		{
			throw new InvalidArgumentException('Signals definitions must be an array.');
		}
		$path = $this->generatedPath . '/' . $this->configFilename;
		$dir = dirname($path);
		if (!is_dir($dir) && !@mkdir($dir, 0777, true) && !is_dir($dir))
		{
			throw new RuntimeException(sprintf('Cannot create signals definition directory "%s".', $dir));
		}
		$contents = PhpExporter::export($data, 'Auto generated, any changes will be lost');
		$temporary = @tempnam($dir, '.signals-');
		if ($temporary === false)
		{
			throw new RuntimeException(sprintf('Cannot create temporary signals definition file in "%s".', $dir));
		}
		try
		{
			// tempnam may fall back to the system temporary directory. Require
			// the destination directory so rename remains an atomic replacement.
			if (dirname($temporary) !== realpath($dir))
			{
				throw new RuntimeException(sprintf('Cannot create a temporary file in "%s".', $dir));
			}
			if (@file_put_contents($temporary, $contents) !== strlen($contents))
			{
				throw new RuntimeException(sprintf('Cannot write signals definition file "%s".', $temporary));
			}
			$mode = is_file($path) ? (fileperms($path) & 0777) : (0666 & ~umask());
			if (!@chmod($temporary, $mode) || !@rename($temporary, $path))
			{
				throw new RuntimeException(sprintf('Cannot replace signals definition file "%s".', $path));
			}
			if (function_exists('opcache_invalidate'))
			{
				opcache_invalidate($path, true);
			}
			return true;
		}
		finally
		{
			if (is_file($temporary))
			{
				unlink($temporary);
			}
		}
	}
}
