<?php

/**
 * This software package is licensed under `AGPL, Commercial` license[s].
 *
 * @package maslosoft/signals
 * @license AGPL, Commercial
 *
 * @copyright Copyright (c) Peter Maselkowski <pmaselkowski@gmail.com>
 * @link https://maslosoft.com/signals/
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

	/**
	 * Processed paths
	 * @var string[]
	 */
	private $paths = [];

	public function __construct(Signal $signal)
	{
		if(!defined('YII_DEBUG'))
		{
			define('YII_DEBUG', true);
		}
		$this->signal = $signal;
	}

	public function generate()
	{
		$extractor = $this->signal->getExtractor();
		$data = $extractor->getData();
		// TODO: Add if extractor instanceof PathsAwareInterface...
		$this->paths = $extractor->getPaths();
		$this->signal->getIO()->write($data);
	}

	public function getPaths()
	{
		return $this->paths;
	}

}
