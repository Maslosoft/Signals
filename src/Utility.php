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

namespace Maslosoft\Signals;

use Maslosoft\Signals\Interfaces\PathsAwareInterface;

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

		if($extractor instanceof PathsAwareInterface)
		{
			$this->paths = $extractor->getPaths();
		}
		$this->signal->getIO()->write($data);
	}

	public function getPaths()
	{
		return $this->paths;
	}

}
