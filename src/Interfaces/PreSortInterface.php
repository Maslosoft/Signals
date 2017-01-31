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

namespace Maslosoft\Signals\Interfaces;

/**
 * Implement this interface to sort signals before creating instances.
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
interface PreSortInterface
{

	public function sort($data);
}
