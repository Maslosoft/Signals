<?php

namespace Emit;

use Codeception\TestCase\Test;
use Maslosoft\Signals\Signal;
use Maslosoft\SignalsTest\Models\ModelWithSlotAwareSignal;
use Maslosoft\SignalsTest\Models\ModelWithSlotAwareSignalOnConstructor;
use Maslosoft\SignalsTest\Signals\SlotAwareSignal;
use Maslosoft\SignalsTest\Signals\SlotAwareSignalForContructor;
use UnitTester;

class SlotAwareTest extends Test
{

	/**
	 * @var UnitTester
	 */
	protected $tester;

	// tests
	public function testIfWillProperlySetSlot()
	{
		$signals = (new Signal())->emit(new SlotAwareSignal);

		$this->assertSame(1, count($signals));

		$signal = current($signals);
		/* @var  $signal SlotAwareSignal */
		$this->assertSame(ModelWithSlotAwareSignal::class, $signal->slotClass);
	}

	public function testIfWillProperlySetSlotOnConstructorInjection()
	{
		$signals = (new Signal())->emit(new SlotAwareSignalForContructor);

		$this->assertSame(1, count($signals));

		$signal = current($signals);
		/* @var  $signal SlotAwareSignalForContructor */
		$this->assertSame(ModelWithSlotAwareSignalOnConstructor::class, $signal->slotClass);
	}

}
