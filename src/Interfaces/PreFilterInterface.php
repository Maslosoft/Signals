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

	public function filter($className, $signal);
}
