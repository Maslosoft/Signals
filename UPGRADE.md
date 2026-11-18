# Upgrading Signals

This branch requires PHP 8.5 or newer. Read the
[migration guide](docs/migration/index.md.php) for dispatcher state isolation,
filter lifetime changes, stricter IO validation, and build exit codes.

Update Composer dependencies, check platform requirements, rebuild generated
connections, and restart long-running workers with the new code and definitions:

```shell
composer update
composer check-platform-reqs
vendor/bin/signals build
```

Applications using multiple dispatchers should explicitly choose between new
instances and context-local `Signal::fly()` reuse. See
[application servers](docs/application-servers/index.md.php) for cleanup boundaries.
