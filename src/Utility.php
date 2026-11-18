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
	private Signal $signal;

	/**
	 * Processed paths
	 * @var string[]
	 */
	private array $paths = [];

	public function __construct(Signal $signal)
	{
		$this->signal = $signal;
	}

	public function generate(): void
	{
		$this->paths = [];
		$extractor = $this->signal->getExtractor();
		$data = $extractor->getData();

		if($extractor instanceof PathsAwareInterface)
		{
			$this->paths = $extractor->getPaths();
		}
		if (!$this->signal->getIO()->write($data))
		{
			throw new \RuntimeException('Could not write the generated signal definitions.');
		}
	}

	public function getPaths(): array
	{
		return $this->paths;
	}

}
