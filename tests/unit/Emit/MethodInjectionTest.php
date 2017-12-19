<?php

namespace Emit;

use Codeception\TestCase\Test;
use Maslosoft\Addendum\Addendum;
use Maslosoft\Addendum\Collections\MatcherConfig;
use Maslosoft\Addendum\Matcher\AnnotationsMatcher;
use Maslosoft\Addendum\Reflection\ReflectionAnnotatedMethod;
use Maslosoft\Signals\Meta\SignalsMeta;
use Maslosoft\Signals\Signal;
use Maslosoft\SignalsTest\Models\Model2WithMethodInjection;
use Maslosoft\SignalsTest\Signals\MethodInjected;
use Maslosoft\SignalsTest\Signals\MethodInjected2;
use Maslosoft\SignalsTest\Signals\MethodInjected3;
use UnitTester;

class MethodInjectionTest extends Test
{

	/**
	 * @var UnitTester
	 */
	protected $tester;

	// tests
	public function testIfWillProperlyEmitSignalWithMethodInjection()
	{

		$m = new ReflectionAnnotatedMethod(Model2WithMethodInjection::class, 'on');
		\Maslosoft\Addendum\Utilities\UseResolver::resolve($m, 'Maslosoft\SignalsTest\Signals\MethodInjected');
		$resolved = \Maslosoft\Addendum\Utilities\UseResolver::resolve($m, 'Maslosoft\SignalsTest\Signals\MethodInjected');

		$parser = new AnnotationsMatcher;
		$data = [];
		$parser->setPlugins(new MatcherConfig([
			'addendum' => Addendum::fly(),
			'reflection' => $m
		]));
		$dc = Addendum::getDocComment($m);
		$parser->matches($dc, $data);
		$data;

		$meta = SignalsMeta::create(Model2WithMethodInjection::class);

		$methodMeta = $meta->method('on');

		codecept_debug($methodMeta);

		$signals = (new Signal())->emit(new MethodInjected);

		$count = 0;
		foreach ($signals as $signal)
		{
			$this->assertTrue($signal->emited);
			$count++;
		}
		$this->assertSame(2, $count);
	}

	public function testIfWillProperlyEmitSignalWithMethodInjectionTwoForeachLoops()
	{
		$signals = (new Signal())->emit(new MethodInjected);

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
		$this->assertSame(4, $count);
	}

	public function testIfWillProperlyEmitSignalWithMethodInjectionOnTwoSlots()
	{
		$signals = (new Signal())->emit(new MethodInjected2);

		$count = 0;
		foreach ($signals as $signal)
		{
			$this->assertTrue($signal->emited);
			$count++;
		}
		$this->assertSame(1, $count);
	}

	public function testIfWillProperlyEmitSignalWithMethodInjectionOnTwoSlotsOnSameClass()
	{
		$signals = (new Signal())->emit(new MethodInjected3);

		$count = 0;
		foreach ($signals as $signal)
		{
			$this->assertTrue($signal->emited);
			$count++;
		}
		$this->assertSame(3, $count);
	}

}
