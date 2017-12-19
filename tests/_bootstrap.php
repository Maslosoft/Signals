<?php

use Maslosoft\Addendum\Addendum;
use Maslosoft\Signals\Signal;
use Maslosoft\Signals\Utility;

date_default_timezone_set('Europe/Paris');

define('VENDOR_DIR', __DIR__ . '/../vendor');
define('YII_DIR', VENDOR_DIR . '/yiisoft/yii/framework/');
require VENDOR_DIR . '/autoload.php';

// Invoker stub for windows
if (defined('PHP_WINDOWS_VERSION_MAJOR'))
{
	require __DIR__ . '/misc/Invoker.php';
}

$config = require __DIR__ . '/config.php';

define('RUNTIME_PATH', __DIR__ . '/runtime');
define('MODELS_PATH', __DIR__ . '/models');
define('SIGNALS_PATH', __DIR__ . '/signals');

$addendum = new Addendum();
$addendum->namespaces[] = 'Maslosoft\\Signals';
$addendum->init();

$signal = new Signal();
$signal->runtimePath = RUNTIME_PATH;
$signal->paths = [
	MODELS_PATH
];
$signal->init();
(new Utility($signal))->generate();
$signal->resetCache();