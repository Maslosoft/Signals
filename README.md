#Maslosoft Signals

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Maslosoft/Signals/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Maslosoft/Signals/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Maslosoft/Signals/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Maslosoft/Signals/?branch=master)
<img src="https://travis-ci.org/Maslosoft/Signals.svg?branch=master" style="height:18px"/>
[![HHVM Status](http://hhvm.h4cc.de/badge/maslosoft/signals.svg)](http://hhvm.h4cc.de/package/maslosoft/signals)

## Wireless cross-component communication

This component allows for interaction of application components, without prior or explicit assignment.

## Setup

Use composer to install

	composer require maslosoft/signals:"*"
	
Or by hard way, download somewhere in your project and ensure autoloading works for `Maslosoft\Signals\*` and you include dep too;
	
Setup signals. After calling `init` any further instance will be configured same as below `$signal`.
	
	$signal = new Maslosoft\Signals\Signal();
	$signal->runtimePath = RUNTIME_PATH;
	$signal->paths = [
		MODELS_PATH
	];
	$signal->init();
	
Generate signals definition, only once, hook it to your build script etc.
		
	$signal = new Maslosoft\Signals\Signal();
	(new Maslosoft\Signals\Utility($signal))->generate();
	
## Usage

### Emiting signal

Define signal:

	<?php
	namespace MyNamespace\Signals;

	class AccountMenuItems extends AdminMenuItems
	{
		public $item = [];	
	}

Define class with slot with `@SlotFor` annotation
	
	<?php
	namespace Maslosoft\Ilmatar\Modules;
	
	class MyModule
	{
		/**
		 * @SlotFor(MyNamespace\Signals\AccountMenuItems)
		 */
		public function reactOnAccountMenu(MyNamespace\Signals\AccountMenuItems $signal)
		{
			$signal->item = [
				'url' => '/content/myBlog',
				'label' => 'My blog'
			];
		}
	}
	


Emit signal and get results of this call:

	<?php
	$signal = new Maslosoft\Signals\Signal();
	$result = $signal->emit(new AdminMenuItems());
	
	echo $result[0]->item[0]['label']; // My blog
		
### Gathering signals