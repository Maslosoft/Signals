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

namespace Maslosoft\Signals\Interfaces;

/**
 * Implement this interface to filter out signals before instantiation.
 *
 * Pre filters will prevent instantiating a slot, as well as appearing it in results of emit.
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
interface PreFilterInterface extends FilterInterface
{

	/**
	 * Return true to allow this element, or false to skip it
	 * @param string $className
	 * @param SignalInterface $signal
	 */
	public function filter($className, $signal);
}
