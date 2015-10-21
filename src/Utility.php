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

namespace Maslosoft\Signals;

/**
 * Signals utility class
 * @codeCoverageIgnore
 * @author Piotr
 */
class Utility
{

	/**
	 * Signal instance
	 * @var Signal
	 */
	private $signal = null;

	public function __construct(Signal $signal)
	{
		$this->signal = $signal;
	}

	public function generate()
	{

		$data = $this->signal->getExtractor()->getData();
		$this->signal->getIO()->write($data);
	}

}
