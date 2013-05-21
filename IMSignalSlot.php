<?php

/**
 * Signal slot interface
 * @author Piotr
 */
interface IMSignalSlot
{

	/**
	 * Set signal comming from application
	 * @param IMSignal $signal
	 */
	public function setSignal(IMSignal $signal);

	/**
	 * Get result of signal
	 */
	public function result();
}