<!--header-->
<!-- Auto generated do not modify between `header` and `/header` -->

# <a href="http://maslosoft.com/signals/">Maslosoft Signals</a>
<a href="http://maslosoft.com/signals/">_Wireless Cross-Component Communication_</a>

<a href="https://packagist.org/packages/maslosoft/signals" title="Latest Stable Version">
<img src="https://poser.pugx.org/maslosoft/signals/v/stable.svg" alt="Latest Stable Version" style="height: 20px;"/>
</a>
<a href="https://packagist.org/packages/maslosoft/signals" title="License">
<img src="https://poser.pugx.org/maslosoft/signals/license.svg" alt="License" style="height: 20px;"/>
</a>
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Maslosoft/Signals/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Maslosoft/Signals/?branch=master)
<img src="https://travis-ci.org/Maslosoft/Signals.svg?branch=master"/>
<img src="https://travis-ci.org/Maslosoft/Signals.svg?branch=master"/>

### Quick Install
```bash
composer require maslosoft/signals:"*"
```

<!--/header-->
## Wireless cross-component communication

This component allows for interaction of application components, without prior or explicit assignment.

## Setup

Use composer to install

```bash
composer require maslosoft/signals:"*"
```
	
Or by hard way, download somewhere in your project and ensure autoloading works for `Maslosoft\Signals\*` and you include dep too;
	
Setup signals. After calling `init` any further instance will be configured same as below `$signal`.

```php
$signal = new Maslosoft\Signals\Signal();
$signal->runtimePath = RUNTIME_PATH;
$signal->paths = [
	MODELS_PATH
];
$signal->init();
```

Generate signals definition, only once, hook it to your build script etc.

```php
$signal = new Maslosoft\Signals\Signal();
(new Maslosoft\Signals\Utility($signal))->generate();
```

## Usage

### Emiting signal

Define signal:

```php
namespace MyNamespace\Signals;

class AccountMenuItems extends AdminMenuItems
{
	public $item = [];
}
```

Define class with slot with `@SlotFor` annotation

```php
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
```


Emit signal and get results of this call:

```php
$signal = new Maslosoft\Signals\Signal();
$result = $signal->emit(new AdminMenuItems());

echo $result[0]->item[0]['label']; // My blog
```

### Gathering signals