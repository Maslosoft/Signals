<?php

namespace Filter;

use Maslosoft\Signals\Signal;
use Maslosoft\SignalsTest\Models\Filters\FalsePostFilter;
use Maslosoft\SignalsTest\Models\Gather\ForFilters\GatherModelWithInstantiatedMarker;
use Maslosoft\SignalsTest\Signals\Slots\GatherFilteredSlot;
use UnitTester;

class GatherPostTest extends \Codeception\TestCase\Test
{

	/**
	 * @var UnitTester
	 */
	protected $tester;

	protected function _before()
	{
		GatherModelWithInstantiatedMarker::$instantiated = false;
		GatherModelWithInstantiatedMarker::$injected = false;
	}

	protected function _after()
	{
		
	}

	// tests

	public function testIfWillEmitProperlyWithoutFilter()
	{
		$result = (new Signal())->gather(new GatherFilteredSlot);

		$this->assertSame(1, count($result), 'That signal was emitted');
		$this->assertTrue(GatherModelWithInstantiatedMarker::$instantiated, 'That instance was created');
	}

	public function testIfWillFilterOutSignalBeforeModelIsCreated()
	{
		$result = (new Signal())->filter(new FalsePostFilter)->gather(new GatherFilteredSlot());

		$this->assertSame(0, count($result), 'That signal was skipped');
		$this->assertTrue(GatherModelWithInstantiatedMarker::$instantiated, 'That instance was created');
	}

	public function testIfWillFilterOutByDefaultFilter()
	{
		$id = 'signals-post-false-filter';
		$result = (new Signal($id))->gather(new GatherFilteredSlot());

		$this->assertSame(0, count($result), 'That signal was skipped');
		$this->assertTrue(GatherModelWithInstantiatedMarker::$instantiated, 'That instance was created');
	}

}
