<title>Gather</title>

# Gather signals

Before gathering signals, [signal definition](../generate/) must be generated.

Gathering is a process somewhat opposite to [emitting signals](../emit/). 
In this scenario, we treat signals as a beacons and `gather` method call
as a receiver for those beacons. Gather more like listens to signals, 
without taking any action by signals.

As mentioned above to gather signals, call `gather` method.

## Setup classes

First create slot class implementing SlotInterface. This will be passed to gather method.

To allow class to be gathered, two criteria must be met:

* Class (or it's partial) must have `@SignalFor` annotation with corresponding slot
* Class (or it's partial) must implement AnnotatedInterface or it will not even be parsed by annotations engine.


## When to use gathering

Gathering is especially usefull fo collecting types, without their interaction. 
This is espacially usefull to place `@SignalFor` on some interface, thus allowing to collect all
implementation in entire application.
