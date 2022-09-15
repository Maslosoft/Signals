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

use Maslosoft\Signals\Interfaces\BuilderIOInterface;
use Maslosoft\Signals\Signal;

/**
 * Memory IO. It does not persist configuration.
 * @codeCoverageIgnore
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class Memory implements BuilderIOInterface
{

	private $data = null;

	public function read(): array
	{
		return $this->data;
	}

	public function setSignal(Signal $signal)
	{

	}

	public function write($data): bool
	{
		$this->data = $data;
		return true;
	}

}
