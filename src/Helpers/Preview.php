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

namespace Maslosoft\Signals\Helpers;

use Maslosoft\Signals\Builder\IO\Memory;
use Maslosoft\Signals\Signal;
use Maslosoft\Signals\Utility;

/**
 * Preview
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class Preview
{

	/**
	 * Processed paths
	 * @var string[]
	 */
	private $paths = [];

	public function cli(Signal $signal)
	{
		$signal->setIO(new Memory());
		$utility = (new Utility($signal));
		$utility->generate();
		$this->paths = $utility->getPaths();
		$data = $signal->getIO()->read();
		return (new Renderer())->renderCli($data);
	}

	public function getPaths()
	{
		return $this->paths;
	}

}
