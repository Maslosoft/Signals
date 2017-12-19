<?php

namespace Filter;

use Maslosoft\Signals\Signal;
use Maslosoft\SignalsTest\Models\Filters\FalsePreFilter;
use Maslosoft\SignalsTest\Models\ForFilters\ModelWithInstantiatedMarker;
use Maslosoft\SignalsTest\Signals\ForFilters\MethodInjectedPre;
use UnitTester;

class PreTest extends \Codeception\TestCase\Test
{

	/**
	 * @var UnitTester
	 */
	protected $tester;

	protected function _before()
	{
		ModelWithInstantiatedMarker::$instantiated = false;
		ModelWithInstantiatedMarker::$injected = false;
	}

	protected function _after()
	{

	}

	// tests

	public function testIfWillEmitProperlyWithoutFilter()
	{
		$result = (new Signal())->emit(new MethodInjectedPre());

		$this->assertSame(1, count($result), 'That signal was emitted');
		$this->assertTrue(ModelWithInstantiatedMarker::$instantiated, 'That instance was created');
		$this->assertTrue(ModelWithInstantiatedMarker::$injected, 'That signal was injected');
	}

	public function testIfWillFilterOutSignalBeforeModelIsCreated()
	{
		$result = (new Signal())->filter(new FalsePreFilter)->emit(new MethodInjectedPre());

		$this->assertSame(0, count($result), 'That signal was skipped');
		$this->assertFalse(ModelWithInstantiatedMarker::$instantiated, 'That instance was not created');
		$this->assertFalse(ModelWithInstantiatedMarker::$injected, 'That signal was not injected');
	}

	public function testIfWillFilterOutSignalBeforeModelIsCreatedFromStringConfig()
	{
		$result = (new Signal())->filter(FalsePreFilter::class)->emit(new MethodInjectedPre());

		$this->assertSame(0, count($result), 'That signal was skipped');
		$this->assertFalse(ModelWithInstantiatedMarker::$instantiated, 'That instance was not created');
		$this->assertFalse(ModelWithInstantiatedMarker::$injected, 'That signal was not injected');
	}

	public function testIfWillFilterOutByDefaultFilter()
	{
		$id = 'signals-pre-false-filter';
		$result = (new Signal($id))->emit(new MethodInjectedPre());

		$this->assertSame(0, count($result), 'That signal was skipped');
		$this->assertFalse(ModelWithInstantiatedMarker::$instantiated, 'That instance was not created');
		$this->assertFalse(ModelWithInstantiatedMarker::$injected, 'That signal was not injected');
	}

}
