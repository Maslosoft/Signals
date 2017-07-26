<?php

use Maslosoft\Ilmatar\Components\Helpers\EnumBase;
use Maslosoft\Ilmatar\Widgets\Messages\MessageType;
use Maslosoft\Signals\Signal;
use Maslosoft\SignalsExamples\Signals\ConstructorInjected;
use Maslosoft\SignalsExamples\Signals\MethodInjected;
use Maslosoft\SignalsExamples\WithConstructorInjection;
use Maslosoft\SignalsExamples\WithMethodInjection;
use Maslosoft\SignalsTest\Models\ModelWithConstructorInjection;
use Maslosoft\Zamm\ShortNamer;
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

*Note: Classes used here are [available in examples folder][examples].*

### Class Constructor injection

When annotation will be placed in class comment block, [Signals][signals] will create instance
of this class and pass emitted signal as constructor param.

Example:

```php
/**
 * @SlotFor(ConstructorInjected)
 *
 * @see ConstructorInjected
 */
class WithConstructorInjection implements AnnotatedInterface
{
...
```

When emitting this is equivalent of following code for each class containing annotation `@SlotFor(ConstructorInjected)`:

<?php
Capture::open();
new WithConstructorInjection(new ConstructorInjected);
echo Capture::close()->md;
?>

Now emit this signal, and see results. This code is really evaluated here:

<?php
Capture::open();
$results = (new Signal)->emit(new ConstructorInjected);
echo Capture::close()->md;
?>

And the resulting array is one instance of signal with it's property set by `WithConstructorInjection` class:

```
<?= print_r($results, true) . PHP_EOL; ?>
```

### Method injection

Possibly most useful injection type. It will instantiate class containing this method and pass
emitted signal as it's parameter to annotated method.

Following class will react on signal, but signal will be injected into `reactOn` method:

```php
class WithMethodInjection implements AnnotatedInterface
{
    /**
     * @SlotFor(MethodInjected)
     * @param MethodInjected $signal
     */
    public function reactOn(MethodInjected $signal)
    {
        $signal->emitted = true;
    }
}
```

When emitting this is equivalent of following code for every annotated method (might be many times for one class):

<?php
Capture::open();
$model = new WithMethodInjection();
$model->reactOn(new MethodInjected());
echo Capture::close()->md;
?>

Emitting this signal will yield array of `MethodInjected` signals. Live evaluation results ara available here:

<?php
Capture::open();
$results = (new Signal)->emit(new MethodInjected);
echo Capture::close()->md;
?>

Array is one instance of signal with it's property set by `WithMethodInjection` class:

```
<?= print_r($results, true) . PHP_EOL; ?>
```

## When to use emitting

Use emit when class instantiated by signal must perform some action.

[signals]: /signals/
[examples]: https://github.com/Maslosoft/Signals/tree/master/examples