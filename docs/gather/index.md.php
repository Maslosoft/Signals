<title>Gathering providers</title>

# Gathering providers

`gather($slot)` finds classes declared with `@SignalFor` for the slot's exact class
and constructs each matching provider without arguments. It returns provider
objects; it does not emit messages or call receiver methods.

## Declare a discovery point

Place each class in its own Composer-autoloadable file. For an `App\` mapping
to `src/`, use `src/Slots/Exporters.php`:

```php
namespace App\Slots;

use Maslosoft\Signals\Interfaces\SlotInterface;

final class Exporters implements SlotInterface
{
}
```

In `src/Export/CsvExporter.php`:

```php
namespace App\Export;

use Maslosoft\Addendum\Interfaces\AnnotatedInterface;

/** @SignalFor(\App\Slots\Exporters) */
final class CsvExporter implements AnnotatedInterface
{
    public function extension(): string
    {
        return 'csv';
    }
}
```

After [generating the definition](../generate/):

```php
use App\Slots\Exporters;
use Maslosoft\Signals\Signal;

foreach (Signal::fly()->gather(new Exporters()) as $exporter) {
    echo $exporter->extension(), PHP_EOL;
}
```

Gatherable classes must implement `AnnotatedInterface` and be concrete. Their
constructors must accept zero arguments. Metadata can be inherited from annotated
parents/interfaces, but it is the discovered concrete classes that are constructed.
Ensure those implementations are in the scan paths and can autoload.

## Restrict returned types

The optional second argument selects providers matching a class or interface:

```php
$providers = Signal::fly()->gather(new Exporters(), App\Export\CsvExporter::class);
```

The restriction can be a concrete class, base class, or interface. Nonmatching
providers are not constructed. A slot with no registered providers returns `[]`.

[Pre-filters](../filters/) inspect the candidate provider class and the slot before
construction. Post-filters receive the constructed provider and can omit it from
the returned array. Constructor side effects have already happened at that point.
