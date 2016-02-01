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
 * Implement this interface to create filter active after signals were instantiated.
 *
 * Post filters will be applied after instantiation of slot and injecting signal.
 * This will only skip this slot from result.
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
interface PostFilterInterface extends FilterInterface
{

	public function filter(SignalInterface $signal);
}
