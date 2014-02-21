<?php

namespace Maslosoft\Signals;

use Maslosoft\Signals\ISignal;

/**
 * Signal slot interface
 * @author Piotr
 */
interface ISignalSlot
{

	/**
	 * Set signal comming from application
	 * @param ISignal $signal
	 */
	public function setSignal(ISignal $signal);

	/**
	 * Get result of signal
	 */
	public function result();
}
