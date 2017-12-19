<?php

namespace Generator;

use Codeception\TestCase\Test;
use Maslosoft\Signals\Signal;
use UnitTester;

class GeneratedTest extends Test
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
	public function testIfSignalsDefinitionsAreGeneratedAndValid()
	{
		$signal = new Signal();
		$path = realpath(sprintf('%s/%s', $signal->getIO()->generatedPath, $signal->getIO()->configFilename));

		$this->assertTrue(file_exists($path));
		$data = require $path;
		$this->assertTrue(is_array($data));

		$this->assertTrue(array_key_exists(Signal::Signals, $data));
		$this->assertTrue(array_key_exists(Signal::Slots, $data));
	}

}
