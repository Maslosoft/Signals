<?php

declare(strict_types=1);

/**
 * Wireless Cross-Component Communication
 * @package maslosoft/signals
 * @license AGPL-3.0-only, proprietary
 * @copyright Copyright (c) Peter Maselkowski <pmaselkowski@gmail.com>
 */

namespace Maslosoft\Signals\Factories;

use Maslosoft\Signals\Interfaces\SlotAwareInterface;
use Maslosoft\Signals\Signal;
use UnexpectedValueException;

class SlotFactory
{
    public static function create(Signal $signals, object $signal, string $fqn, string|bool $injection): object|false
    {
        // Apply the same missing-class behavior to constructor, method and property injection.
        if (!class_exists($fqn)) {
            $signals->getLogger()->debug('Class `{class}` not found while emitting signal `{signal}`', [
                'class' => $fqn,
                'signal' => $signal::class,
            ]);
            return false;
        }
        $cloned = clone $signal;
        if ($injection === true) {
            $slot = new $fqn($cloned);
            // The slot does not exist until its constructor completes.
            if ($cloned instanceof SlotAwareInterface) {
                $cloned->setSlot($slot);
            }
            return $cloned;
        }
        if (!is_string($injection) || $injection === '') {
            throw new UnexpectedValueException('An injection must be true, a property name, or a method name ending in ().');
        }
        $slot = new $fqn();
        if ($cloned instanceof SlotAwareInterface) {
            $cloned->setSlot($slot);
        }
        if (str_ends_with($injection, '()')) {
            $method = substr($injection, 0, -2);
            if (!is_callable([$slot, $method])) {
                throw new UnexpectedValueException(sprintf('Slot method %s::%s is not callable.', $fqn, $method));
            }
            $slot->$method($cloned);
        } else {
            if (!property_exists($slot, $injection) && !method_exists($slot, '__set') && !$slot instanceof \stdClass) {
                throw new UnexpectedValueException(sprintf('Slot property %s::$%s is not declared.', $fqn, $injection));
            }
            $slot->$injection = $cloned;
        }
        return $cloned;
    }
}
