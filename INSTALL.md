# Installation

Use PHP 8.5 or newer:

```shell
composer require maslosoft/signals
composer check-platform-reqs
```

Configure scan paths and generate the definition before dispatching. Follow the
[configuration guide](docs/configuration/index.md.php) and
[generation guide](docs/generate/index.md.php). The
[emission guide](docs/emit/index.md.php) contains a complete receiver example.

For a development checkout, run `composer install` and `composer check`.
Swoole/OpenSwoole are optional and require versions compatible with PHP 8.5.
