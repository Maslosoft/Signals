<?php
/**
 * Created by PhpStorm.
 * User: peter
 * Date: 26.07.17
 * Time: 19:17
 */

namespace Maslosoft\SignalsExamples\Signals;


use Maslosoft\Signals\Interfaces\SignalInterface;

class ConstructorInjected implements SignalInterface
{
	public $emitted = false;
}