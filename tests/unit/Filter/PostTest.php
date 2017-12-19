<?php

namespace Filter;

use Maslosoft\Signals\Signal;
use Maslosoft\SignalsTest\Models\Filters\FalsePostFilter;
use Maslosoft\SignalsTest\Models\ForFilters\ModelWithInstantiatedMarker;
use Maslosoft\SignalsTest\Signals\ForFilters\MethodInjectedPost;
use UnitTester;

class PostTest extends \Codeception\TestCase\Test
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
		$result = (new Signal())->emit(new MethodInjectedPost());

		$this->assertSame(1, count($result), 'That signal was emitted');
		$this->assertTrue(ModelWithInstantiatedMarker::$instantiated, 'That instance was created');
		$this->assertTrue(ModelWithInstantiatedMarker::$injected, 'That signal was injected');
	}

	public function testIfWillFilterOutSignalBeforeModelIsCreated()
	{
		$result = (new Signal())->filter(new FalsePostFilter)->emit(new MethodInjectedPost());

		$this->assertSame(0, count($result), 'That signal was skipped');
		$this->assertTrue(ModelWithInstantiatedMarker::$instantiated, 'That instance was created');
		$this->assertTrue(ModelWithInstantiatedMarker::$injected, 'That signal was injected');
	}

	public function testIfWillFilterOutByDefaultFilter()
	{
		$id = 'signals-post-false-filter';
		$result = (new Signal($id))->emit(new MethodInjectedPost());

		$this->assertSame(0, count($result), 'That signal was skipped');
		$this->assertTrue(ModelWithInstantiatedMarker::$instantiated, 'That instance was created');
		$this->assertTrue(ModelWithInstantiatedMarker::$injected, 'That signal was injected');
	}

}
