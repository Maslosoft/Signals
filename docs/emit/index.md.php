<title>Emitting signals</title>

# Emitting signals

`emit($message)` finds receivers registered for the message's exact class, clones
the message for each injection, invokes that receiver, and returns the resulting
clones. It returns an empty array when there are no matching receivers.

## A complete receiver

Make application classes autoloadable with Composer. For these examples, map
`App\` to `src/`, and place each class in the corresponding file.

In `src/Messages/MenuItems.php`:

```php
namespace App\Messages;

use Maslosoft\Signals\Interfaces\SignalInterface;

final class MenuItems implements SignalInterface
{
    public array $items = [];
}
```

In `src/Receivers/BlogMenu.php`:

```php
namespace App\Receivers;

use App\Messages\MenuItems;
use Maslosoft\Addendum\Interfaces\AnnotatedInterface;

final class BlogMenu implements AnnotatedInterface
{
    /** @SlotFor(\App\Messages\MenuItems) */
    public function contribute(MenuItems $message): void
    {
        $message->items[] = ['label' => 'Blog', 'url' => '/blog'];
    }
}
```

Concrete annotated receivers must implement Addendum's `AnnotatedInterface`.
`SignalInterface` is a useful marker for messages, but it does not replace
`AnnotatedInterface` on classes scanned for annotations. These are docblock
annotations, not PHP attributes. PHP snippets omit the opening tag; add that tag
when creating a standalone source file.

Add `src` to `paths` in `signals.yml`, then [build the definition](../generate/).
In application code after loading `vendor/autoload.php`:

```php
use App\Messages\MenuItems;
use Maslosoft\Signals\Signal;

$original = new MenuItems();
$results = Signal::fly()->emit($original);

foreach ($results as $message) {
    foreach ($message->items as $item) {
        echo $item['label'], PHP_EOL;
    }
}
// $original->items is still [] in this example.
```

`emit(MenuItems::class)` also works when the message has no required constructor
arguments. Pass an object when it needs initial data.

## Injection choices

| Annotation location | Construction and injection |
| --- | --- |
| Class docblock | Construct the receiver with the cloned message as its constructor argument |
| Public method docblock | Construct the receiver without arguments, then call the method with the clone |
| Public property docblock | Construct the receiver without arguments, then assign the clone to that property |

Each registered injection creates a separate receiver and clone, even when several
methods belong to the same receiver class. Method and property injection require a
receiver constructible without required arguments. Constructor injection requires
a constructor accepting the message. These receiver objects are created directly;
EmbeDi configures the dispatcher and its adapters, not arbitrary receiver services.

For constructor injection:

```php
namespace App\Receivers;

use App\Messages\MenuItems;
use Maslosoft\Addendum\Interfaces\AnnotatedInterface;

/** @SlotFor(\App\Messages\MenuItems) */
final class AccountMenu implements AnnotatedInterface
{
    public function __construct(MenuItems $message)
    {
        $message->items[] = ['label' => 'Account', 'url' => '/account'];
    }
}
```

Property injection assigns data; it does not invoke a method. Declare the public
property explicitly to avoid PHP dynamic properties. Prefer method injection when
the receiver must perform work immediately.

## Clone and slot awareness

PHP cloning is shallow. Nested objects remain shared unless the message implements
`__clone()` to copy them. Do not assume each receiver has an independent object
graph merely because the outer message was cloned.

A message implementing `SlotAwareInterface` receives the receiver through
`setSlot($slot)`. With method/property injection, this happens before injection.
With constructor injection it happens after the receiver constructor returns, so
the constructor cannot rely on the message already knowing its receiver.

Receiver exceptions propagate and stop the dispatch. Earlier receiver side
effects remain. [Pre-filters](../filters/) can prevent receiver construction;
post-filters only remove completed results.
