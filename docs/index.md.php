<title>Signals</title>

# Signals

Signals connects application components discovered through annotations. An emitter
does not need to know which packages receive its message. A gatherer can discover
the concrete classes offered by installed packages.

Signals requires **PHP 8.5 or newer**. Install it with Composer:

```shell
composer require maslosoft/signals
```

## Two ways to connect components

| Operation | Declaration | Result |
| --- | --- | --- |
| `emit($message)` | Receiver uses `@SlotFor(MessageClass)` | One cloned message per injection, after the receiver runs |
| `gather($slot)` | Provider uses `@SignalFor(SlotClass)` | Newly constructed provider objects |

Dispatch is synchronous. Exceptions from receivers and filters propagate to the
caller. Signals does not provide queues, retries, or transactions. Each dispatch
uses the exact message or slot class in the generated definition; inheritance
alone does not register additional runtime keys.

## Build before dispatching

Configure directories containing Composer-autoloadable classes in `signals.yml`:

```yaml
paths:
  - src
io:
  class: Maslosoft\Signals\Builder\IO\PhpFile
  generatedPath: generated
```

After adding [receivers](emit/) or [gatherable providers](gather/), run from the
application root:

```shell
composer dump-autoload
vendor/bin/signals build
```

Deploy `generated/signals-definition.php` alongside the matching application code.
It can be committed or produced as a build artifact. The build user needs write
access to its destination; request handlers only need read access. CLI YAML
configuration also produces a cache under `runtime/`; see
[configuration](configuration/) for its deployment requirements.

At runtime, load Composer's autoloader and reuse a dispatcher within the current
execution context:

```php
use Maslosoft\Signals\Signal;

$signals = Signal::fly();
```

Use [filters](filters/) to select participants,
[generation](generate/) to manage discovery and deployment, and
[application servers](application-servers/) to choose request lifetimes.
