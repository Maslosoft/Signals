<title>Configuration</title>

# Configuration

Configure Signals before calling `init()`, `emit()`, or `gather()`. The constructor
applies EmbeDi configuration first, then reads Signals YAML/PHP configuration.
Direct property assignments made afterwards take precedence.

## Direct configuration

```php
use Maslosoft\Signals\Builder\IO\PhpFile;
use Maslosoft\Signals\Signal;

$io = new PhpFile();
$io->generatedPath = __DIR__ . '/generated';

$signals = new Signal();
$signals->paths = [__DIR__ . '/src'];
$signals->setIO($io);
$signals->init();
```

Absolute paths are useful in workers whose working directory differs from the
application root. `init()` loads the definition once and stores the public
configuration in the named EmbeDi container. `resetCache()` reloads this
dispatcher's definition; it does not run the source scanner.

| Property | Default | Purpose |
| --- | --- | --- |
| `paths` | `['vendor']` | Directories scanned when generating definitions |
| `ignoreDirs` | `['vendor', 'generated', 'runtime']` | Nested directory names excluded by the scanner |
| `filters` | `[]` | Filters applied to every dispatch on this dispatcher |
| `extractor` | `Builder\Addendum::class` | Extractor class, EmbeDi definition array, or instance |
| `io` | `Builder\IO\PhpFile::class` | Definition reader/writer class, definition array, or instance |
| `runtimePath` | `'runtime'` | Directory for the PHP configuration cache; configure through EmbeDi before construction when changing where it is read |
| `sorters` | `[]` | Reserved legacy setting; custom runtime sorting is not implemented |

Attach a PSR-3 logger with `setLogger($logger)` to see missing classes and discovery
diagnostics. The default logger is silent. Generated entries are sorted by name
for deterministic output; receiver order should not encode application dependencies.

## YAML configuration

The default configuration name is `signals`, so CLI execution reads `signals.yml`:

```yaml
paths:
  - src
  - vendor/acme/plugins
ignoreDirs:
  - vendor
  - generated
  - runtime
extractor:
  class: Maslosoft\Signals\Builder\Addendum
io:
  class: Maslosoft\Signals\Builder\IO\PhpFile
  generatedPath: generated
  configFilename: signals-definition.php
filters: []
```

`generatedPath` belongs to `io`; `runtimePath` belongs to the dispatcher. CLI
execution prefers a nonempty `signals.yml` and otherwise reads
`runtime/signals.php`. Non-CLI execution reads the PHP cache. Both files must
contain an array of configuration values.

Construction only reads configuration. The `build` command writes the PHP
configuration cache after successful definition generation; `preview`, `emit()`,
and `gather()` do not refresh it. Provision the cache during deployment or
configure Signals directly through EmbeDi/application bootstrap. The cache is
separate from `generated/signals-definition.php`, which stores connections.

To change where the constructor reads cached configuration, supply `runtimePath`
through EmbeDi before constructing Signals. Setting it directly afterwards cannot
change a read that has already happened. A YAML `runtimePath` changes the build
output destination; web bootstrap must select that same path through EmbeDi.
Relative configuration paths resolve against `APPLICATION_ROOT` when defined,
otherwise the current working directory. Use absolute paths when these differ
between build and request processes. Definition IO paths are configured separately.

## EmbeDi adapters and named dispatchers

EmbeDi 3.0.1 or newer can adapt framework configuration. Register adapters in the
execution context that will construct the dispatcher:

```php
use Maslosoft\EmbeDi\Adapters\ArrayAdapter;
use Maslosoft\EmbeDi\EmbeDi;
use Maslosoft\Signals\Builder\IO\PhpFile;
use Maslosoft\Signals\Signal;

EmbeDi::fly()->addAdapter(new ArrayAdapter([
    'signals' => [
        'class' => Signal::class,
        'io' => [
            'class' => PhpFile::class,
            'generatedPath' => __DIR__ . '/generated',
        ],
    ],
]));
$signals = Signal::fly();
```

YAML/PHP configuration loaded by the constructor can override adapter values.
Use one configuration source where possible. `new Signal('admin-signals')` selects
a different EmbeDi identity and configuration basename. Give separately configured
dispatchers distinct names; their public configuration snapshots are named in
EmbeDi. Loaded definitions and attached overlays belong to each dispatcher object.

`Signal::fly()` explicitly reuses the same dispatcher in the current Light context.
`new Signal()` creates another object. See
[application servers](../application-servers/) for cleanup and coroutine boundaries.

## Additional definitions

`attach($definition)` merges an overlay into this dispatcher's loaded data using
`array_replace_recursive`. Keys are fully qualified class names with a leading
backslash. This example suppresses one receiver:

```php
$signals->attach([
    Signal::Signals => [
        '\\App\\Messages\\MenuItems' => [
            '\\App\\Receivers\\BlogMenu' => false,
        ],
    ],
]);
```

`attach($definition, false)` queues the overlay until the next reload. Overlays
remain attached when `resetCache()` reloads the base file. Use a fresh dispatcher
when you need an independent set of overlays. See [storage adapters](php-io/)
for in-memory definitions and custom readers.
