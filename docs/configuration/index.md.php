<?php

use Maslosoft\Signals\Signal;
use Maslosoft\Zamm\ShortNamer;
use Maslosoft\Zamm\Widgets\PropertiesDocs;

?>
<?php
ShortNamer::defaults()->md();
$name = new ShortNamer(Signal::class);

?>
<title>1. Configuration</title>

# Configuration

When using default settings configuration is not nessesary.

## Configurable options


<?= new PropertiesDocs(Signal::class); ?>


## Configuration types

Signals can be configured by directly setting up named instance, by configuration adapters or via `signals.yml` file.

### Direct configuration

To directly configure - instantiate signals - with optional instance name.
Then set available public properties to required values and then call `init`.

### Configuration via adapters

For more details about adaptes visit [EmbeDi project documentation](/embedi/docs/).

### Configuration via `signals.yml`

All options available in <?= $name; ?> can also be configured in `signals.yml`
file in project root.

Example configuration file:

```yaml
<?= file_get_contents(__DIR__ . '/signals.yml'); ?>
```

[php-io]: php-io/