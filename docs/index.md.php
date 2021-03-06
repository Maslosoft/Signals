<title>Documentation</title>

# About

Signals are a kind of connector between scattered application components.
These components are supposed to be dynamically linked, mostly by installing them via composer.

## Quick start

Install via composer:

```shell
composer require "maslosoft/signals"
```

### Required directories

Signals require writable directory `generated` in application root. This directory should be committed
together with codebase. Another required writable directory is `runtime` for temporary files, this should
not be committed, and it's contents can be safely cleared.

##### In Short Summary

Required writable directories:

 * `generated` - commit this
 * `runtime` - do not commit this

### Generate signals

Before use codebase need to be [scanned for signals and slots](generate/). 

To [generate](generate/) issue command:

```shell
vendor/bin/signals build
```

From now on signals can be [emitted](emit/) and [gathered](gather/) within
your code.

Check [this repository for working example of Signals][repo]

To customize available options head to [configuration](configuration/) secion.


[repo]: https://github.com/MaslosoftGuides/signals.quick-start