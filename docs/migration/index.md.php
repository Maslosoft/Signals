<title>Migration to PHP 8.5</title>

# Migration to PHP 8.5

This modernization requires PHP 8.5 or newer. PHP 7.x, earlier PHP 8.x versions,
and HHVM are no longer supported. Install dependencies on the same PHP version
used in production and check the deployment platform:

```shell
composer install
composer check-platform-reqs
```

EmbeDi now requires at least 3.0.1, and Maslosoft Light is a runtime dependency.
Use the dependency constraints and lockfile as the authority for supported package
versions. Development tooling targets PHP 8.5 too. Swoole and OpenSwoole remain
optional; ordinary PHP execution is supported.

## Dispatcher state

Loaded definitions and `attach()` overlays previously lived in process-wide static
arrays. They now belong to each `Signal` object. Code relying on an overlay added
to one object affecting every other object must pass the configured dispatcher
explicitly or use `Signal::fly()` consistently within the same Light context.

`new Signal()` still constructs a separate object. `Signal::fly()` reuses one by
configuration name within the current context. `Signal::reset()` removes cached
Signals dispatchers in that context without changing existing references or
clearing EmbeDi. See [application servers](../application-servers/) for job cleanup.

Configured IO/extractor objects are retained by their dispatcher. Replacing IO
with `setIO()` invalidates the loaded definition. `resetCache()` reloads the base
map while retaining attached overlays. Empty maps are initialized once rather
than reread on every dispatch.

## Filters and invalid receivers

Temporary filters now apply to the next `emit()` **or** `gather()` and are consumed
even when no participant exists or a callback throws. Nested dispatches do not
inherit the outer operation's temporary filters. `filter(false)` clears queued
temporary filters, not configured filters.

Missing receiver classes are skipped consistently, including constructor injection.
Invalid method targets and undeclared property targets now fail explicitly instead
of silently succeeding or creating dynamic properties. Declare public injectable
properties, or provide the receiver's intentional `__set()` behavior.

## Generation and IO

`Memory::read()` returns `[]` before its first write. `PhpFile` requires snapshots
to return arrays and replaces written files atomically. Invalid snapshots and
filesystem failures are visible errors. `Utility::generate()` throws if a custom
writer returns `false`, and no longer defines the unrelated `YII_DEBUG` constant.

Repeated scans start with fresh data. Generator reflection no longer uses `eval`
or installs a process-wide temporary autoloader. The deprecated
`Maslosoft\Signals\Helpers\NameNormalizer` has been removed. Replace its imports
and calls with `Maslosoft\Addendum\Utilities\NameNormalizer`, which owns class-name
normalization and updates the supplied string in place.

The `build` command returns exit code `0` after success. Update scripts that worked
around its old nonzero success status. Rebuild your definition after upgrading,
and deploy it with the corresponding application code.

## Configuration and documentation

Remove `io.runtimePath` from old YAML: it is not a `PhpFile` setting. The dispatcher
`runtimePath` now controls its PHP configuration-cache directory. Configure it
through EmbeDi before construction when changing the read location. A YAML setting
changes the build output location, so web bootstrap must agree with it.

Construction no longer writes configuration caches implicitly. The build command
persists its configuration cache explicitly after generating definitions. Preview
and runtime dispatch are read-only. Programmatic `Utility::generate()` only writes
the definition. Read [configuration](../configuration/) before changing deployment.

Public dispatcher properties and methods now have PHP types. Update subclasses
to use compatible signatures and supply values matching those types.

Documentation pages remain in `docs/**/index.md.php` and no longer require Zamm or
application-specific widgets to render. They contain ordinary Markdown with title
metadata. Existing PHP annotation syntax remains supported; this update does not
convert Signals annotations into PHP attributes.

Some supported upstream packages can still emit PHP 8.5 deprecations during
annotation discovery or configuration export. These are separate from Signals' runtime fixes; do not
hide all deprecations to mask them. Track upstream updates and rerun the
[development checks](../development/) when changing dependency versions.
