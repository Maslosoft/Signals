<?php

use Maslosoft\Ilmatar\Components\Helpers\EnumBase;
use Maslosoft\Ilmatar\Widgets\Messages\MessageType;
use Maslosoft\SignalsExamples\Signals\ConstructorInjected;
use Maslosoft\SignalsExamples\WithConstructorInjection;
use Maslosoft\SignalsTest\Models\ModelWithConstructorInjection;
use Maslosoft\Zamm\Source;
use Maslosoft\Zamm\Capture;
?>
<title>3. Emit</title>

# Emit signal

Prior to emitting signal, [signal definition](../generate/) must be generated.

Emitting signal is a process where single point in application
emerges action and other, scattered around application receivers take action after
receiving such signal.

To emit signal simply call `emit` method with some signal as param.

## Receiving signals

Signals could be received via various injection methods. This depends on receiver implementation.
To create receiver use annotation `@SlotFor` on either class, method or property definition.
Depending on where annotation is placed, it will be injected properly by signals.

### Class Constructor injection

When annotation will be placed in class comment block, [Signals][signals] will create instance
of this class and pass emitted signal as constructor param.

Example:

```php
/**
 * Model with constructor injection
 * @SlotFor(ConstructorInjected)
 *
 * @see ConstructorInjected
 */
class WithConstructorInjection implements AnnotatedInterface
{
...
```

When emitting this is equivalent of following code:

<?php
Capture::open();
new WithConstructorInjection(new ConstructorInjected);
echo Capture::close()->md;
?>

### Method injection

Possibly most useful injection type. It will instantiate class containing this method and pass
emitted signal as it's param.

## When to use emitting

Use emit when class instantiated by signal must perform some action.

[signals]: /signals/