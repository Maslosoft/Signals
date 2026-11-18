<title>Definition storage</title>

# Definition storage

`PhpFile` reads and writes the connection array used by `emit()` and `gather()`.
Its defaults are `generatedPath = 'generated'` and
`configFilename = 'signals-definition.php'`. A filename may include subdirectories.
Use absolute paths when dispatchers run outside the application root.

```php
use Maslosoft\Signals\Builder\IO\PhpFile;
use Maslosoft\Signals\Signal;

$io = new PhpFile();
$io->generatedPath = __DIR__ . '/generated';
$io->configFilename = 'signals-definition.php';
$signals = new Signal();
$signals->setIO($io);
$signals->init();
```

The writer creates missing directories and replaces the destination atomically.
Filesystem failures throw an exception instead of reporting a successful build.
The reader returns an empty array for a missing file and logs a debug message
when attached to a dispatcher. An existing file must return an array; malformed
snapshots are rejected. PHP syntax errors in a snapshot also propagate.

The definition is executable PHP. Store it in a deployment-controlled location,
and deploy it with the source code it describes. Rebuild after classes or
annotations change. Already initialized dispatchers keep their loaded definitions
until `resetCache()` or replacement. The writer invalidates the replaced file's
OPcache entry when OPcache is available. Worker restarts provide a deployment
boundary for both code and configuration changes.

## In-memory definitions

Use one `Memory` instance for generation and dispatch when no file is needed:

```php
use Maslosoft\Signals\Builder\IO\Memory;
use Maslosoft\Signals\Signal;

$memory = new Memory();
$memory->write([Signal::Signals => [], Signal::Slots => []]);
$signals = new Signal();
$signals->setIO($memory);
$signals->init();
```

A new `Memory` adapter reads as `[]`. Data belongs to that adapter object; a new
adapter starts empty. Sharing a mutable adapter between requests also shares its
contents, regardless of Light context isolation.

## Custom adapters

Implement `BuilderIOInterface`: `read(): array`, `write($data): bool`, and
`setSignal(Signal $signal)`. Return the same definition structure as `PhpFile`.
Keep a custom adapter's bound `Signal` reference private; EmbeDi snapshots public
configuration, and a public owner reference introduces a cycle. Reserve public
properties for configurable values. Keep persistence errors visible to the build caller. A writer can throw or return
`false`; `Utility::generate()` treats a failed write as a failed generation.
