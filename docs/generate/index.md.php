<title>Generating definitions</title>

# Generating definitions

Generation scans configured source directories for `@SlotFor` and `@SignalFor`
metadata and writes the connection map. Run it when installing/removing packages
or changing annotations and participating classes, as part of build or deployment.
Runtime dispatch reads that map and does not scan source files.

## Command line

From the application root:

```shell
composer dump-autoload
vendor/bin/signals preview -vv
vendor/bin/signals build
```

`preview` scans and displays connections without replacing the definition file;
`-vv` also lists processed paths. Preview does not persist a configuration cache.
`build` saves the definition and PHP configuration cache, and returns exit status
`0` on success. Filesystem failures cause a failed command.

Configure `paths` in [signals.yml](../configuration/) to include application code
and the vendor packages that provide extensions. All discovered classes must be
autoloadable before scanning. Concrete classes with Signals annotations must
implement `Maslosoft\Addendum\Interfaces\AnnotatedInterface`.

The `ignoreDirs` setting excludes matching directory names during traversal. A
`.signalignore` file causes files directly in that directory to be skipped by the
extractor. Do not assume it is a recursive ignore rule for every descendant.

## Programmatic generation

```php
use Maslosoft\Signals\Builder\IO\PhpFile;
use Maslosoft\Signals\Signal;
use Maslosoft\Signals\Utility;

$io = new PhpFile();
$io->generatedPath = __DIR__ . '/generated';

$signals = new Signal();
$signals->paths = [__DIR__ . '/src'];
$signals->setIO($io);

$utility = new Utility($signals);
$utility->generate();
$signals->resetCache();
$processedFiles = $utility->getPaths();
```

`Utility::generate()` extracts data and writes through the configured IO adapter.
It does not persist the separate YAML/PHP configuration cache; use application
bootstrap configuration or the CLI build command when that cache is required.
Reload an already initialized dispatcher afterwards. Reusing an extractor for
another scan starts with fresh definitions and processed paths.

## Deployment and diagnosis

Ship the generated PHP file with the exact code version it describes. Multiple
workers can read the same immutable artifact. Build it before workers start;
creating one dispatcher per request is not a reason to regenerate the file per
request. Restart long-running workers or explicitly reload their dispatchers when
deploying a new definition.

If a receiver is missing, check its Composer mapping, scan path, marker interface,
annotation target, and class/method visibility. Attach a PSR-3 logger during
programmatic builds to expose scanning diagnostics. Some discovery errors are
logged while scanning continues, so inspect the output as well as the command's
exit code. Invalid snapshots are rejected by the [storage adapter](../configuration/php-io/).
