<?php

namespace Helpers;

use Codeception\TestCase\Test;
use Maslosoft\Addendum\Utilities\NameNormalizer;
use UnitTester;

class NameNormalizerTest extends Test
{

	use \Codeception\Specify;

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
	public function testIfWillNormalizeClassName()
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
			$this->specify($title, function() use($src, $dest)
			{
				NameNormalizer::normalize($src);
				$this->assertSame($dest, $src);
			});
		}
	}

}
