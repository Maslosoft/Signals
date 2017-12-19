<?php

namespace Emit;

use Codeception\TestCase\Test;
use Maslosoft\Addendum\Interfaces\AnnotatedInterface;
use Maslosoft\Signals\Signal;
use Maslosoft\SignalsTest\Models\Gather\BaseClassForModelWithBaseClass;
use Maslosoft\SignalsTest\Models\Gather\GatherTestInterface;
use Maslosoft\SignalsTest\Models\Gather\ModelWithSlot;
use Maslosoft\SignalsTest\Models\Gather\ModelWithSlotAndBaseClass;
use Maslosoft\SignalsTest\Models\Gather\ModelWithSlotAndInterface;
use Maslosoft\SignalsTest\Signals\Slots\GatherSlot;
use UnitTester;

class GatherTest extends Test
{

	/**
	 * @var UnitTester
	 */
	protected $tester;

	protected function _before()
	{
		
	}

	protected function _after()
	{
		
	}

	// tests
	public function testIfWillProperlyGatherSignals()
	{
		$signals = (new Signal())->gather(new GatherSlot);

		$count = 0;
		foreach ($signals as $signal)
		{
			$this->assertInstanceof(AnnotatedInterface::class, $signal);
			$count++;
		}
		$this->assertSame(3, $count);
	}

	public function testIfWillProperlyGatherSignalsOfSelectedInterface()
	{
		$signals = (new Signal())->gather(new GatherSlot, GatherTestInterface::class);

		$count = 0;
		foreach ($signals as $signal)
		{
			$this->assertInstanceof(ModelWithSlotAndInterface::class, $signal);
			$this->assertInstanceof(GatherTestInterface::class, $signal);
			$count++;
		}
		$this->assertSame(1, $count);
	}

	public function testIfWillProperlyGatherSignalsOfSelectedBaseClass()
	{
		$signals = (new Signal())->gather(new GatherSlot, BaseClassForModelWithBaseClass::class);

		$count = 0;
		foreach ($signals as $signal)
		{
			$this->assertInstanceof(ModelWithSlotAndBaseClass::class, $signal);
			$this->assertInstanceof(BaseClassForModelWithBaseClass::class, $signal);
			$count++;
		}
		$this->assertSame(1, $count);
	}

	public function testIfWillProperlyGatherSignalsOfSameClass()
	{
		$signals = (new Signal())->gather(new GatherSlot, ModelWithSlot::class);

		$count = 0;
		foreach ($signals as $signal)
		{
			$this->assertInstanceof(ModelWithSlot::class, $signal);
			$count++;
		}
		$this->assertSame(1, $count);
	}

}
