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

use Maslosoft\Signals\Signal;

/**
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
interface SignalAwareInterface
{

	public function setSignal(Signal $signal);
}
