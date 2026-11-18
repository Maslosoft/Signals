# Working on Signals

Signals is a PHP 8.5+ library for generated annotation-based component dispatch.

## Skills

- Use [.agents/signals/SKILL.md](.agents/signals/SKILL.md) for application integration.
- Use [.agents/signals-dev/SKILL.md](.agents/signals-dev/SKILL.md) for library changes.

Keep both skills current in the same change as relevant behavior, requirements,
or workflow changes. Keep this file current when repository conventions change.

## Runtime boundaries

Loaded definitions, attached overlays, temporary filters, and configured adapters
belong to each `Signal` object. `Signal::fly()` uses Light's public context API for
intentional reuse by concrete class and configuration name. Do not reintroduce
process-global mutable dispatch state. Immutable generated files can be shared
across request contexts and should be built during deployment. Addendum discovery
retains upstream global metadata state; do not run it concurrently in requests.
Configuration construction is read-only. Only the explicit build command persists
the PHP configuration cache; EmbeDi supplies `runtimePath` before reads.

`Signal::reset()` clears Signals flyweights in the current context only. Existing
references survive. EmbeDi owns its separate context-scoped configuration and
cleanup. Native coroutines are separate; plain Fibers and sequential workers need
application-managed boundaries. Test the actual runtime for native isolation claims.

## Development

Use PHP >=8.5 and the requirements in `composer.json`; preserve the EmbeDi 3.0.1
minimum and Light runtime dependency. Install with `composer install` and run:

```shell
composer check
composer validate --strict
composer audit
```

Individual scripts include `test:build`, `test`, `test:coverage`, `lint`, and
`analyse`. PHPStan targets PHP 8.5. Run focused regressions for behavior changes
and the relevant standard checks. Verify lowest permitted dependencies in an
isolated checkout when changing constraints. Do not edit installed vendor files.

Fixtures under `tests/models` and `tests/signals` must autoload for the annotation
scanner. Keep generated runtime/output files out of source control. Restore custom
Light configuration and clean temporary artifacts in tests. Skip unavailable native
coroutine engines explicitly rather than treating mocks as native coverage.

## Documentation

Maintain `docs/**/index.md.php` pages with title metadata and ordinary Markdown.
The documentation is wrapped by its own UI: do not add documentation-home links,
back links, or breadcrumbs. Contextual topic links are useful. Do not embed PHP
opening tags in fenced examples: the renderer would execute them. Avoid requiring
Zamm or application-specific widgets. Run meaningful changed examples.

Document application-facing behavior changes in the migration guide, especially
state lifetimes, filtering, configuration precedence, and generated-file handling.
