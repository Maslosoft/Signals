<?php

use Maslosoft\Addendum\Addendum;
use Maslosoft\Signals\Signal;
use Maslosoft\Signals\Utility;

date_default_timezone_set('Europe/Paris');

const VENDOR_DIR = __DIR__ . '/../vendor';
require VENDOR_DIR . '/autoload.php';

// Invoker stub for windows
if (defined('PHP_WINDOWS_VERSION_MAJOR'))
{
	require __DIR__ . '/misc/Invoker.php';
}
const TESTS_CONFIG_PATH = __DIR__ . '/config.php';
$config = require TESTS_CONFIG_PATH;

const RUNTIME_PATH = __DIR__ . '/runtime';
const MODELS_PATH = __DIR__ . '/models';
const SIGNALS_PATH = __DIR__ . '/signals';

$addendum = new Addendum();
$addendum->namespaces[] = 'Maslosoft\\Signals';
$addendum->init();

$signal = new Signal();

echo "Signals " . $signal->getVersion() . PHP_EOL;
echo "Using config: " . TESTS_CONFIG_PATH . PHP_EOL;

$signal->runtimePath = RUNTIME_PATH;
$signal->paths = [
	MODELS_PATH
];
$signal->init();
(new Utility($signal))->generate();
$signal->resetCache();

error_reporting(E_ALL);