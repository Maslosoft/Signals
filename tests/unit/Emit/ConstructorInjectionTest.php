<?php

namespace Emit;

use Codeception\TestCase\Test;
use Maslosoft\Signals\Signal;
use Maslosoft\SignalsTest\Signals\ConstructorInjected;
use UnitTester;

class ConstructorInjectionTest extends Test
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
	public function testIfWillProperlyEmitSignalWithConstructorInjection()
	{
		$signals = (new Signal())->emit(new ConstructorInjected);


		$count = 0;
		foreach ($signals as $signal)
		{
			$this->assertTrue($signal->emited);
			$count++;
		}
		$this->assertSame(2, $count);
	}

}
