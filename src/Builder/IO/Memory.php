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

	public function read()
	{
		return $this->data;
	}

	public function setSignal(Signal $signal)
	{
		
	}

	public function write($data)
	{
		$this->data = $data;
		return true;
	}

}
