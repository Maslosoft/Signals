<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
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
