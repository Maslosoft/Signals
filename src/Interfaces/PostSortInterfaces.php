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
 * Implement this interface to sort signals after they are instantiated.
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
interface PostSortInterfaces
{

	public function sort($data);
}
