---
name: signals-dev
description: Maintain the Maslosoft Signals library, including dispatch internals, annotation generation, dependencies, PHP compatibility, tests, documentation, and scoped application-server state. For application integration, use signals.
---

# Developing Signals

Read [AGENTS.md](../../AGENTS.md) and the relevant source before changing behavior.
Keep PHP 8.5 as the minimum, EmbeDi's required minimum at 3.0.1 or newer, and Light
as a runtime dependency. Use Composer metadata for current versions rather than
copying a changing package inventory into this skill.

## Preserve architectural boundaries

- `Signal` owns loaded definitions, attached overlays, pending filter state, and
  instantiated IO/extractor adapters. Independent objects must not share loaded
  maps implicitly. EmbeDi's named public configuration is a distinct concern.
- `Signal::fly()` uses Light's public context API for reuse by concrete class and
  configuration name. `Signal::reset()` clears that library namespace only;
  existing references and other Light/EmbeDi entries survive. Do not use a global
  backend switch or process-global registry to share one dispatcher.
- Keep build artifacts separate from request state. Generated PHP files may be
  shared across contexts; discovery belongs in build/deployment, not flyweight
  construction. Mutable explicitly supplied dependencies remain object references.
- `emit()` returns shallow clones per injection, not receiver objects. Check
  missing classes consistently for constructor/method/property injection. Do not
  silently create dynamic properties or treat a missing method as success.
- Temporary filters are consumed for emit/gather, including empty and exceptional
  paths. Nested calls have independent temporary filters; configured filters
  remain active. Post-filtering cannot undo receiver side effects.
- Preserve public extension contracts with Addendum metadata and IO/filter
  interfaces. Parent property/method types must match the installed dependencies.
- Addendum owns class-name normalization through
  `Maslosoft\Addendum\Utilities\NameNormalizer`. The deprecated
  `Maslosoft\Signals\Helpers\NameNormalizer` has been removed; use the upstream
  utility directly.

Configuration reads must remain free of cache writes: only the explicit build
command persists the PHP configuration cache. EmbeDi configuration precedes that
read so `runtimePath` can select its directory. Programmatic generation writes
only definitions. Addendum has upstream global metadata state; do not claim Light
makes concurrent annotation generation request-safe.

## Generation and persistence

Repeated extractor scans start with fresh maps and path lists. Keep temporary
reflection/error handling scoped, restoring it on failures; avoid `eval` and
process-wide autoloader state. Classes with annotations must autoload and implement
`AnnotatedInterface`; concrete implementations carry inherited metadata.

Custom IO/extractors should keep their bound dispatcher reference private to avoid
cycles when EmbeDi snapshots public configuration. A fresh Memory adapter returns `[]`. PHP snapshots must return arrays. Writes
replace snapshots atomically, invalidate relevant OPcache state, and surface
filesystem failures. `Utility::generate()` must reject a writer returning `false`.
Build commands return zero on success and propagate failure exit status. Preview
must restore the original IO after either successful or failed extraction.

## Validate the changed behavior

Use PHP >=8.5 and the Composer scripts in [development](../../docs/development/index.md.php).
Run focused regression tests, then the relevant `composer check` checks. Keep
PHPStan's PHP 8.5 target. Verify locked and lowest-permitted dependencies in
separate checkouts when constraints change; do not hide dependency incompatibility
with vendor edits or blanket deprecation suppression.

Exercise reuse, independent objects, sequential reset, and unrelated context data
for state changes. Native Swoole/OpenSwoole claims require the real installed
engine; explicitly skip unavailable engines. A custom backend test alone does not
prove native integration. Restore backend configuration and temporary artifacts.

For injection/filter changes cover affected modes, cloning, and empty/throwing/
nested operations. For generator/IO changes cover repeated scans, invalid files,
and failed writes. Fixture classes under `tests/models` and `tests/signals` must
remain autoloadable so tests exercise real discovery.

## Keep maintenance guidance current

Update this skill and [.agents/signals](../signals/SKILL.md) in the same change as
relevant behavior, requirements, or workflow changes. Update `AGENTS.md` when
repository conventions change, and [migration](../../docs/migration/index.md.php)
for compatibility breaks. Avoid volatile test counts in maintained guidance.

Keep docs in their existing `docs/**/index.md.php` format with title metadata.
The external UI supplies navigation: omit home links, breadcrumbs, and back links.
Do not put executable PHP opening tags in fenced examples. Use contextual topic
links and run meaningful changed examples against the installed dependencies.
