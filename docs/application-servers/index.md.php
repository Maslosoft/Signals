<title>Application servers</title>

# Application servers

Signals uses Maslosoft Light for explicit dispatcher reuse. Choose a dispatcher
lifetime that matches the request or job owning its mutable state.

## Object state and context state

| State | Owner |
| --- | --- |
| Loaded definition, attached overlays, pending filters, IO/extractor instances | Individual `Signal` object |
| Dispatchers returned by `Signal::fly()` | Current Light context, concrete dispatcher class, configuration name |
| EmbeDi adapters and public configuration snapshots | EmbeDi's current Light context |
| Generated PHP definition file | Deployment artifact shared by its readers |

`new Signal()` always creates a separate dispatcher. Its loaded map and overlays
do not change an existing object's map. Constructors with the same configuration
name can still receive the same public configuration through EmbeDi. Mutable
objects explicitly supplied in configuration remain references; they are not
cloned by Light.

```php
use Maslosoft\Signals\Signal;

$signals = Signal::fly();
assert($signals === Signal::fly());
assert($signals !== new Signal());
```

`Signal::reset()` removes Signals' cached dispatcher references in the current
context. It does not mutate existing dispatcher objects, clear EmbeDi, or remove
unrelated Light entries. Release old references after a request ends.

## Sequential workers

Outside active native coroutines, Light's default backend persists for the PHP
execution. A queue worker or sequential application server therefore needs an
explicit job boundary. Register configuration inside that boundary and clean it
up in `finally`:

```php
use Maslosoft\EmbeDi\EmbeDi;
use Maslosoft\Signals\Signal;

function runJob(callable $handle): mixed
{
    try {
        // Register this job's EmbeDi adapters here, before constructing Signals.
        $signals = Signal::fly();
        return $handle($signals);
    } finally {
        Signal::reset();
        EmbeDi::reset();
    }
}
```

This example assumes the application owns the entire EmbeDi lifetime for each
job. `EmbeDi::reset()` also clears configuration belonging to other EmbeDi users
in the current context. Re-register the required adapters for the next job. If
those components intentionally outlive jobs, use a suitable application-owned
configuration strategy instead of clearing their state implicitly.

`Maslosoft\Light\Context::clear()` is broader still: it clears all Light entries
in the current context, including other libraries. Use it only when the application
is ending that whole scope. Plain PHP Fibers do not receive automatic isolation
from the default Light backends.

## Swoole and OpenSwoole

With a compatible extension, each active native coroutine has its own Light
context. Construct or obtain dispatchers and register EmbeDi adapters inside the
request coroutine. Parent and child coroutines have separate registries; child
coroutines do not inherit the parent's configuration registration.

For Swoole, a minimal lifetime example is:

```php
use Maslosoft\EmbeDi\EmbeDi;
use Maslosoft\Signals\Signal;

Swoole\Coroutine\run(function (): void {
    try {
        $signals = Signal::fly();
        $results = $signals->emit(new App\Messages\MenuItems());
    } finally {
        Signal::reset();
        EmbeDi::reset();
    }
});
```

In a server that already runs the request in a coroutine, put the body directly
in its request handler. OpenSwoole follows the same lifetime rule with its native
coroutine API. Extension versions must support PHP 8.5 or newer.

Capturing a pre-created dispatcher, filter, or IO object in multiple coroutines
shares that exact object. A scoped registry does not isolate externally shared
objects or framework globals. Obtain `Signal::fly()` inside each owning context.

## Configuration in workers

Swoole and other CLI workers still have `PHP_SAPI === 'cli'`, so constructors
prefer a nonempty YAML configuration when present. Construction is read-only:
only the explicit build command writes the PHP configuration cache. Request
handlers can read a deployment-provided definition and configuration without
writing cache files.

Configure a custom `runtimePath` through EmbeDi before constructing Signals so
non-CLI workers read the intended cache. Use absolute IO paths when the worker's
working directory differs from the application root. Register request-specific
adapters inside the owning context.

## Builds and deployment

Addendum's annotation machinery has upstream state outside Signals' context
registry. Do not run discovery concurrently inside requests; Light isolation of
dispatchers does not make the scanner request-safe.

Generate definitions once in your existing build/deployment workflow. The file
can be shared by all request contexts. Repeated `fly()` calls should not trigger
scanning or publication. Restart workers after deploying code/configuration, or
reload each retained dispatcher with `resetCache()` when only definitions change.
A custom Light backend test verifies context behavior; it does not by itself prove
native coroutine integration.
