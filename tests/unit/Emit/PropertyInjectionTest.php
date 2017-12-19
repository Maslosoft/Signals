<?php

namespace Emit;

use Codeception\TestCase\Test;
use Maslosoft\Signals\Signal;
use Maslosoft\SignalsTest\Signals\PropertyInjected;
use Maslosoft\SignalsTest\Signals\PropertyInjected2;
use UnitTester;

class PropertyInjectionTest extends Test
{

	/**
	 * @var UnitTester
	 */
	protected $tester;

	// tests
	public function testIfWillProperlyEmitSignalWithPropertyInjection()
	{
		$signals = (new Signal())->emit(new PropertyInjected);

		$count = 0;
		foreach ($signals as $signal)
		{
			$this->assertTrue($signal->emited);
			$count++;
		}
		$this->assertSame(2, $count, 'That two slots were created');
	}

	public function testIfWillProperlyEmitSignalWithPropertyInjectionTwoForeachLoops()
	{
		$signals = (new Signal())->emit(new PropertyInjected());

		$count = 0;

		foreach ($signals as $signal)
		{
			$this->assertTrue($signal->emited);
			$count++;
		}
		foreach ($signals as $signal)
		{
			$this->assertTrue($signal->emited);
			$count++;
		}
		$this->assertSame(4, $count, 'That two loops succeed (totalling in 4 items)');
	}

	public function testIfWillProperlyEmitSignalWithPropertyInjectionOnTwoSlots()
	{
		$signals = (new Signal())->emit(new PropertyInjected2);

		$count = 0;
		foreach ($signals as $signal)
		{
			$this->assertTrue($signal->emited);
			$count++;
		}
		$this->assertSame(1, $count);
	}

}
