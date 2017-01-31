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
 * This interface is meant to be used when defining signals.
 * Implement this to automatically pass calling object to your signal.
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
interface SlotAwareInterface
{

	/**
	 * Set slot to be available in signal.
	 *
	 * Example implementation:
	 * ```php
	 *
	 * ...
	 * // Owner of this signal
	 * public $owner = null;
	 *
	 * public function setSlot($slot)
	 * {
	 * 		if($slot instance of MyCompatibleInterface)
	 * 		{
	 * 			$this->owner = $slot;
	 * 			// So something with owner
	 * 		}
	 * }
	 * ```
	 * @param object $slot
	 */
	public function setSlot($slot);
}
