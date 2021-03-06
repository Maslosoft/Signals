<?php

use Maslosoft\Ilmatar\Components\Helpers\EnumBase;
use Maslosoft\Ilmatar\Widgets\Messages\MessageType;
use Maslosoft\SignalsTest\Models\ModelWithConstructorInjection;
use Maslosoft\Zamm\ShortNamer;
use Maslosoft\Zamm\Source;
use Maslosoft\Zamm\Capture;
use Maslosoft\Signals\Utility;
use Maslosoft\Zamm\Namer;
use Maslosoft\Signals\Signal;
?>
<?php
ShortNamer::defaults()->md();
$s = new ShortNamer(Signal::class);
$u = new ShortNamer(Utility::class);
?>
<title>2. Generate</title>

# Generate signals definition

Before using signals there must be available information about whole
application signals and slots.
This can be generated by signals command line, or
programmatically in target application.

## Generate signals via command line

To build signals definition call below command in application root:

```shell
vendor/bin/signals build
```

This will scan application sources for signals related annotations
and define definition file.
Scanned sources folders are configurable
via <?= $s->paths; ?> ([also by yml and adapters][cfg]):

## Generate signals programatically

Signals can also be generated programatically by <?= $u->generate();?> method:

```php
(new Utility(new Signal()))->generate();
```

##### Performance notices

1. Generating might take some time - should be performed only when updating/installing/removing application components
2. Generated file is PHP type - will be automatically cached by opcache or APC


[cfg]: ../configuration/