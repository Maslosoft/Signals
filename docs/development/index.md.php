<title>Developing Signals</title>

# Developing Signals

Use PHP 8.5 or newer for Composer, the CLI, tests, and analysis. The Composer
scripts select the current PHP interpreter; check `php -v` if several are installed.

```shell
composer install
composer check
```

The individual checks are:

```shell
composer test:build
composer test
composer lint
composer analyse
composer validate --strict
composer audit
```

`composer test` runs the Codeception unit suite. The bootstrap builds a definition
from the test models; runtime/output directories are disposable. `composer
analyse` runs PHPStan against the configured PHP 8.5 target. Use
`composer test:coverage` when a compatible coverage driver is installed.

## Source map

| Location | Responsibility |
| --- | --- |
| `src/Signal.php` | Dispatch, configuration, context-local dispatcher reuse |
| `src/Factories/` and `src/Helpers/` | Injection, filtering, ordering, CLI presentation |
| `src/Builder/` | Annotation discovery and definition storage |
| `src/Meta/` and `src/Options/` | Addendum metadata integration |
| `src/Application/` | Symfony Console commands |
| `tests/models/`, `tests/signals/` | Autoloadable discovery fixtures |
| `tests/unit/` | Behavior and regression coverage |

## Tests that protect behavior

For dispatch changes, cover all affected injection modes, clone behavior, missing
classes, and filters on normal, empty, exception, and nested paths. For state
changes, distinguish independent dispatcher instances from `Signal::fly()` reuse
and ensure sequential job cleanup does not clear unrelated Light data.

Native coroutine tests must run against the real installed engine and explicitly
skip when it is absent. Custom contexts are useful for deterministic boundaries
but are not evidence of Swoole/OpenSwoole compatibility. Restore changed context
configuration and clean temporary files after tests.

For generation changes, exercise repeated scans, autoload failures, annotation
metadata, and both Memory and PHP-file adapters. Check that exceptions do not
leave process-global error handling or autoloaders changed. Never edit `vendor/`
to make checks pass.

## Dependency and compatibility changes

Retain the PHP 8.5 lower bound and the required EmbeDi/Light generations. Verify
both the lockfile installation and the lowest allowed versions when changing
constraints. The CI matrix exercises locked and lowest dependencies on PHP 8.5;
check the workflow for current details. Use a temporary checkout for lowest-version
experiments so the normal lockfile is not replaced accidentally.

Upstream annotation/export packages can emit deprecations on PHP 8.5.
Identify their source before changing Signals; keep warnings visible and distinguish
an upstream notice from a regression in this repository.

## Documentation and skills

Read `AGENTS.md` and `.agents/signals-dev/SKILL.md` for maintenance conventions.
Keep `.agents/signals/SKILL.md` aligned with application-facing behavior and
`.agents/signals-dev/SKILL.md` aligned with architecture and development workflows
in the same change. Update the migration guide for behavior or compatibility breaks.

Pages in `docs/` are wrapped by the documentation UI. Keep the existing
`index.md.php` convention and title metadata. Use contextual topic links without
home links, breadcrumbs, or back links. Avoid PHP opening tags in fenced examples,
since the page is processed as PHP; snippets represent source after the opening tag.
Render changed pages without application widgets and execute meaningful changed
examples against the installed dependency versions.
