<title>Filters</title>

# Filters

Filters select which participants run and which results are returned. Configure
`$signals->filters` for every operation on a dispatcher, or call
`$signals->filter($filter)` for its next `emit()` or `gather()`.

## Before construction

A `PreFilterInterface` implementation receives a candidate class name and the
original message (for emit) or slot (for gather). Returning `false` prevents
construction and injection, so this is the appropriate place to prevent unwanted
receiver work.

```php
use Maslosoft\Signals\Interfaces\PreFilterInterface;

final class AllowReceivers implements PreFilterInterface
{
    public function __construct(private array $allowed)
    {
    }

    public function filter($className, $signal): bool
    {
        return in_array(ltrim($className, '\\'), $this->allowed, true);
    }
}

$signals->filter(new AllowReceivers([App\Receivers\BlogMenu::class]));
$results = $signals->emit(new App\Messages\MenuItems());
```

Definition keys have a leading backslash; class constants do not. Normalize names
before comparing them, as the example does.

## After construction

A `PostFilterInterface` implementation receives a processed message clone for
emit, or a constructed provider for gather. The runtime also supplies the original
message/slot as a second argument; implementations may accept it as an optional
parameter while retaining compatibility with the one-argument interface.

```php
use Maslosoft\Signals\Interfaces\PostFilterInterface;

final class HasMenuItems implements PostFilterInterface
{
    public function filter($result, $original = null): bool
    {
        return $result instanceof App\Messages\MenuItems && $result->items !== [];
    }
}
```

Returning `false` removes the result only. It cannot undo construction, receiver
calls, external writes, or other side effects.

## Configuration and lifetime

```php
$signals->filters = [HasMenuItems::class];
$results = $signals->emit(new App\Messages\MenuItems());
```

Configured filters accept class names, EmbeDi definition arrays, or existing filter
objects. A class configured by name must be constructible without required
arguments. Pass an object when a filter requires constructor data. Existing filter
objects remain shared references wherever the application passes them.

Temporary filters are consumed by the next emit or gather, including an empty
result or an exception. Nested dispatches do not inherit the outer operation's
temporary filters. Configured filters still apply to each operation.

`filter(false)` clears pending temporary filters. It does **not** disable the
configured `filters` list. To remove persistent filters, change that list on the
dispatcher you own. Filter exceptions propagate to the caller.
