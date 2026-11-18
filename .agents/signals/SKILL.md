---
name: signals
description: Use Maslosoft Signals in PHP applications, including annotation discovery, emit and gather, filtering, configuration, and application-server lifetimes. For changes to the Signals library itself, use signals-dev.
---

# Using Signals in applications

Use the installed library source and Composer requirements as the API authority.
This checkout targets PHP 8.5+, EmbeDi 3.0.1+, and Light 2. Resolve this skill's real
path when installed through a symlink; the linked guides live in its library checkout.

## Choose the operation and lifetime

- `emit($message)` matches its exact class, creates a receiver and shallow message
  clone for each injection, and returns processed clones. Class-level `@SlotFor`
  injects via constructor; method/property annotations construct without arguments.
- `gather($slot, $classOrInterface = null)` matches the slot's exact class and
  constructs registered providers without arguments. Declare providers with
  `@SignalFor`; the optional type restriction excludes nonmatching providers.
- Concrete annotated classes must implement Addendum's `AnnotatedInterface` and
  autoload. `SignalInterface`/`SlotInterface` are separate semantic markers, not
  substitutes for scanner participation. Annotations remain docblocks, not attributes.
- `new Signal()` owns its loaded map, overlays, pending filters, and configured
  adapters. Use `Signal::fly()` for deliberate reuse within the current Light
  context. Same-name constructors can still share public configuration via EmbeDi.

## Integrate and build

Make participants Composer-autoloadable, configure scan paths, and generate with
`vendor/bin/signals build` during build/deployment. Ship the resulting definition
with the corresponding source. Do not scan or publish once per request context.

The constructor applies EmbeDi configuration followed by Signals YAML/PHP config;
subsequent direct assignments override both. Configure before dispatch/init.
`signals.yml` is preferred under CLI; non-CLI execution reads
`runtime/signals.php`. Construction is read-only. The build command persists the
configuration cache explicitly; `Utility::generate()` only writes definitions.
This cache differs from `generated/signals-definition.php`. Set `runtimePath`
through EmbeDi before construction to change the cache read location. YAML can
change build output, so runtime bootstrap must agree. `io.runtimePath` is invalid.
Addendum discovery has upstream global state: keep scanning at build time.

A missing definition reads empty; a malformed existing snapshot fails. Use a
PSR-3 logger to diagnose discovery/missing targets. After rebuilding, replace
workers or call `resetCache()` on retained dispatchers. Overlays remain attached.

## Preserve dispatch semantics

Temporary `filter()` entries affect the next emit or gather, even empty/throwing
operations, and do not flow into nested dispatch. `filter(false)` clears queued
filters, not the configured filter list. Pre-filters prevent construction;
post-filters only omit results after side effects. Existing filter objects and
nested message objects remain shared references unless explicitly copied.

Receiver exceptions propagate; earlier side effects remain. Slots are constructed
directly, not resolved as application services by EmbeDi. With constructor
injection, `SlotAwareInterface::setSlot()` runs after the constructor returns.
Declare injectable properties; do not depend on accidental dynamic properties.

Use `Maslosoft\Addendum\Utilities\NameNormalizer` when normalizing class names.
The deprecated `Maslosoft\Signals\Helpers\NameNormalizer` has been removed.

## Application servers

Register adapters and obtain mutable dispatchers inside the request coroutine.
Native Swoole/OpenSwoole coroutines, including children, have independent contexts;
bootstrap outside them is not inherited. Passing a pre-created object still shares
it. Plain Fibers receive no automatic isolation.

Outside native coroutines, end sequential job lifetimes explicitly.
`Signal::reset()` clears only Signals' cached dispatcher references in the current
context; old references survive. EmbeDi cleanup is separate and affects all its
users in that context. Use `EmbeDi::reset()` in `finally` only at an
application-owned EmbeDi boundary. `Context::clear()` is broader still.

## Read the relevant guide

- [Emission](../../docs/emit/index.md.php): receiver declarations and cloning.
- [Gathering](../../docs/gather/index.md.php): providers and type restrictions.
- [Configuration](../../docs/configuration/index.md.php): adapters, names, overlays.
- [Filters](../../docs/filters/index.md.php): pre/post contracts and lifetime.
- [Generation](../../docs/generate/index.md.php): scanning and deployment.
- [Application servers](../../docs/application-servers/index.md.php): context boundaries.
- [Migration](../../docs/migration/index.md.php): compatibility changes.

Keep this skill aligned when application-facing behavior changes. Library
implementation work belongs in [signals-dev](../signals-dev/SKILL.md).
