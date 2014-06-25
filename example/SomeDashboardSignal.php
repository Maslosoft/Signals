<?php

use Maslosoft\Signals\ISignal;

/**
 * SomeDashboardSignal
 * @SignalFor('SomeDashboardController')
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class SomeDashboardSignal implements ISignal
{

	public function getName()
	{
		return __CLASS__;
	}

	public function run()
	{
		return 'Another slot signal';
	}

}
