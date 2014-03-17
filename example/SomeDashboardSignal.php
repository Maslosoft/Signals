<?php

use Maslosoft\Signals\ISignal;

/**
 * SomeDashboardSignal
 * @SignalFor('DashboardController')
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class SomeDashboardSignal implements ISignal, DashboardWidget
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
