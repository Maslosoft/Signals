<?php

namespace Helpers;

use Codeception\Test\Unit;
use Maslosoft\Addendum\Utilities\NameNormalizer;
use UnitTester;

class NameNormalizerTest extends Unit
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

	/**
	 * TODO Move to addendum project
	 */
	public function testIfWillNormalizeClassName(): void
	{
		$config = [
			'Some\\Namespace\\' => '\\Some\\Namespace',
			'\\Some\\Namespace' => '\\Some\\Namespace',
			'\\Some\\\\Namespace' => '\\Some\\Namespace',
			'GlobalNamespace' => '\\GlobalNamespace',
			'\\GlobalNamespace' => '\\GlobalNamespace',
			'\\GlobalNamespace\\' => '\\GlobalNamespace',
		];

		foreach ($config as $src => $dest)
		{
			$title = sprintf('Namespace `%s` should be `%s`', $src, $dest);
			NameNormalizer::normalize($src);
			$this->assertSame($dest, $src, $title);
		}
	}

}
