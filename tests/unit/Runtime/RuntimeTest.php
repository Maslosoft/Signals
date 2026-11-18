<?php

declare(strict_types=1);

namespace Runtime;

use Codeception\Test\Unit;
use Maslosoft\EmbeDi\EmbeDi;
use Maslosoft\Light\Context;
use Maslosoft\Light\ContextManager;
use Maslosoft\Light\Interfaces\ContextInterface;
use Maslosoft\Signals\Builder\IO\Memory;
use Maslosoft\Signals\Interfaces\BuilderIOInterface;
use Maslosoft\Signals\Interfaces\PreFilterInterface;
use Maslosoft\Signals\Signal;
use RuntimeException;
use UnexpectedValueException;

final class RuntimeTest extends Unit
{
    private array $contexts;

    protected function _before(): void
    {
        $this->contexts = ContextManager::$contexts;
        TestContext::$scope = 'first';
        TestContext::$values = [];
        ContextManager::$contexts = [TestContext::class];
    }

    protected function _after(): void
    {
        ContextManager::$contexts = $this->contexts;
        TestContext::$values = [];
    }

    private function dispatcher(array $data = []): Signal
    {
        $signal = new Signal('signals-runtime-regression');
        $io = new Memory();
        $io->write($data);
        $signal->setIO($io);
        return $signal;
    }

    private function definitions(string $injection = 'receive()'): array
    {
        return [
            Signal::Signals => ['\\' . Message::class => ['\\' . Receiver::class => [$injection]]],
            Signal::Slots => ['\\' . Message::class => ['\\' . Provider::class => true]],
        ];
    }

    public function testDefinitionsAndAttachedOverlaysBelongToTheirDispatcher(): void
    {
        $first = $this->dispatcher($this->definitions());
        $second = $this->dispatcher();
        $first->attach([Signal::Signals => ['\\' . Message::class => ['\\' . Receiver::class => false]]]);
        self::assertSame([], $first->emit(new Message()));
        self::assertSame([], $second->emit(new Message()));
        $second->attach($this->definitions());
        self::assertCount(1, $second->emit(new Message()));
        self::assertSame([], $first->emit(new Message()));
        $first->resetCache();
        self::assertCount(1, $second->emit(new Message()));
    }

    public function testEmptyDefinitionsAreLoadedOnceUntilExplicitReload(): void
    {
        $signal = $this->dispatcher();
        $io = new CountingIO();
        $signal->setIO($io);
        $signal->emit(new Message());
        $signal->gather(new Message());
        $signal->init();
        self::assertSame(1, $io->reads);
        $signal->resetCache();
        self::assertSame(2, $io->reads);
    }

    public function testConfiguredMemoryAdapterIsReused(): void
    {
        $signal = new Signal('signals-runtime-memory');
        $signal->io = Memory::class;
        $signal->getIO()->write($this->definitions());
        self::assertSame($signal->getIO(), $signal->getIO());
        self::assertCount(1, $signal->emit(new Message()));
    }

    public function testCachedAdaptersDoNotReplaceTheirSerializableConfiguration(): void
    {
        $signal = new Signal('signals-runtime-public-owner');
        $signal->io = PublicOwnerIO::class;
        $io = $signal->getIO();
        $signal->init();
        self::assertSame(PublicOwnerIO::class, $signal->io);
        self::assertSame($io, $signal->getIO());
        self::assertSame($signal, $io->signal);
        $signal->io = Memory::class;
        self::assertNotSame($io, $signal->getIO());
    }

    public function testTemporaryFiltersAreConsumedByEmptyDispatchAndGather(): void
    {
        $signal = $this->dispatcher($this->definitions());
        self::assertSame([], $signal->filter(new Reject())->emit(new \stdClass()));
        self::assertCount(1, $signal->emit(new Message()));
        self::assertSame([], $signal->filter(new Reject())->gather(new Message()));
        self::assertCount(1, $signal->gather(new Message()));
        self::assertSame([], $signal->filter(new Reject())->gather(new \stdClass()));
        self::assertCount(1, $signal->gather(new Message()));
    }

    public function testTemporaryFiltersAreConsumedAfterExceptions(): void
    {
        $signal = $this->dispatcher($this->definitions());
        foreach (['emit', 'gather'] as $method) {
            try {
                $signal->filter(new ThrowingFilter())->$method(new Message());
                self::fail('The filter must throw.');
            } catch (RuntimeException $exception) {
                self::assertSame('filter failed', $exception->getMessage());
            }
            self::assertCount(1, $signal->$method(new Message()));
        }
    }

    public function testNestedDispatchDoesNotConsumeTheOuterFilterFrame(): void
    {
        $signal = $this->dispatcher($this->definitions());
        $filter = new NestedFilter($signal);
        self::assertCount(1, $signal->filter($filter)->emit(new Message()));
        self::assertSame(1, $filter->calls);
        self::assertSame(1, $filter->nestedCount);
        self::assertCount(1, $signal->emit(new Message()));
        self::assertSame(1, $filter->calls);
    }

    public function testObjectFiltersRemainConfiguredWhenTemporaryFiltersAreCleared(): void
    {
        $signal = $this->dispatcher($this->definitions());
        $signal->filters = [new Reject()];
        self::assertSame([], $signal->filter(false)->emit(new Message()));
        self::assertSame([], $signal->gather(new Message()));
    }

    public function testGatherAcceptsClassAndInterfaceConstraints(): void
    {
        $signal = $this->dispatcher($this->definitions());
        self::assertCount(1, $signal->gather(new Message(), ProviderContract::class));
        self::assertCount(1, $signal->gather(new Message(), Provider::class));
        self::assertCount(1, $signal->gather(new Message(), '\\' . Provider::class));
        self::assertSame([], $signal->gather(new Message(), Receiver::class));
    }

    public function testMissingConstructorTargetIsSkipped(): void
    {
        $signal = $this->dispatcher([Signal::Signals => ['\\' . Message::class => ['\\NoSuchSignalsRuntimeClass' => [true]]]]);
        self::assertSame([], $signal->emit(new Message()));
    }

    public function testInvalidMethodDoesNotReportSuccessfulInjection(): void
    {
        $signal = $this->dispatcher($this->definitions('missing()'));
        $this->expectException(UnexpectedValueException::class);
        $signal->emit(new Message());
    }

    public function testUndeclaredPropertyDoesNotCreateADynamicProperty(): void
    {
        $signal = $this->dispatcher($this->definitions('missing'));
        $this->expectException(UnexpectedValueException::class);
        $signal->emit(new Message());
    }

    public function testContextReuseIsolationAndSelectiveReset(): void
    {
        $first = Signal::fly('signals-runtime-context');
        $first->setIO(new Memory());
        $first->attach($this->definitions());
        self::assertSame($first, Signal::fly('signals-runtime-context'));
        self::assertNotSame($first, Signal::fly('signals-runtime-other'));
        Context::put('application-owned', 'keep');
        $di = EmbeDi::fly();
        TestContext::$scope = 'second';
        $second = Signal::fly('signals-runtime-context');
        $second->setIO(new Memory());
        self::assertNotSame($first, $second);
        self::assertSame([], $second->emit(new Message()));
        TestContext::$scope = 'first';
        self::assertSame($first, Signal::fly('signals-runtime-context'));
        Signal::reset();
        self::assertNotSame($first, Signal::fly('signals-runtime-context'));
        self::assertSame('keep', Context::get('application-owned'));
        self::assertSame($di, EmbeDi::fly());
        self::assertCount(1, $first->emit(new Message()));
        TestContext::$scope = 'second';
        self::assertSame($second, Signal::fly('signals-runtime-context'));
    }

    public function testNativeSwooleContextsAreIsolated(): void
    {
        if (!extension_loaded('swoole')) {
            $this->markTestSkipped('Swoole is not installed.');
        }
        $this->nativeIsolation('Swoole');
    }

    public function testNativeOpenSwooleContextsAreIsolated(): void
    {
        if (!extension_loaded('openswoole')) {
            $this->markTestSkipped('OpenSwoole is not installed.');
        }
        $this->nativeIsolation('OpenSwoole');
    }

    private function nativeIsolation(string $engine): void
    {
        ContextManager::$contexts = ContextManager::DefaultContexts;
        $runner = $engine . '\\Coroutine\\run';
        $coroutine = $engine . '\\Coroutine';
        $channel = $engine . '\\Coroutine\\Channel';
        $runner(function () use ($coroutine, $channel): void {
            $parent = Signal::fly('signals-native-regression');
            $parent->setIO(new Memory());
            $parent->attach($this->definitions());
            $coroutine::create(function () use ($parent): void {
                $child = Signal::fly('signals-native-regression');
                $child->setIO(new Memory());
                self::assertNotSame($parent, $child);
                self::assertSame([], $child->emit(new Message()));
            });
            self::assertSame($parent, Signal::fly('signals-native-regression'));
            self::assertCount(1, $parent->emit(new Message()));

            $ready = new $channel(1);
            $continue = new $channel(1);
            $complete = new $channel(1);
            $coroutine::create(function () use ($ready, $continue, $complete): void {
                $first = Signal::fly('signals-native-regression');
                $first->setIO(new Memory());
                $first->attach($this->definitions());
                $first->filter(new Reject());
                $ready->push($first);
                $continue->pop();
                self::assertSame($first, Signal::fly('signals-native-regression'));
                self::assertSame([], $first->emit(new Message()));
                self::assertCount(1, $first->emit(new Message()));
                $complete->push(true);
            });
            $coroutine::create(function () use ($ready, $continue): void {
                $first = $ready->pop();
                $second = Signal::fly('signals-native-regression');
                $second->setIO(new Memory());
                $second->attach($this->definitions());
                self::assertNotSame($first, $second);
                self::assertCount(1, $second->emit(new Message()));
                Signal::reset();
                self::assertNotSame($second, Signal::fly('signals-native-regression'));
                $continue->push(true);
            });
            $complete->pop();
            self::assertSame($parent, Signal::fly('signals-native-regression'));
        });
    }
}

final class Message
{
    public bool $received = false;
}

final class Receiver
{
    public function receive(Message $message): void
    {
        $message->received = true;
    }
}

interface ProviderContract {}
final class Provider implements ProviderContract {}

final class Reject implements PreFilterInterface
{
    public function filter($className, $signal): bool { return false; }
}

final class ThrowingFilter implements PreFilterInterface
{
    public function filter($className, $signal): bool { throw new RuntimeException('filter failed'); }
}

final class NestedFilter implements PreFilterInterface
{
    public int $calls = 0;
    public int $nestedCount = 0;

    public function __construct(private Signal $dispatcher) {}

    public function filter($className, $signal): bool
    {
        ++$this->calls;
        $this->nestedCount = count($this->dispatcher->emit(new Message()));
        return true;
    }
}

final class CountingIO implements BuilderIOInterface
{
    public int $reads = 0;
    public function read(): array { ++$this->reads; return []; }
    public function write($data): bool { return true; }
    public function setSignal(Signal $signal): void {}
}

final class TestContext implements ContextInterface
{
    public static string $scope = 'first';
    public static array $values = [];
    public static function isAvailable(): bool { return true; }
    public static function has(string|int $key): bool { return array_key_exists($key, self::$values[self::$scope] ?? []); }
    public static function get(string|int $key): mixed { return self::$values[self::$scope][$key] ?? null; }
    public static function put(string|int $key, mixed $value): void { self::$values[self::$scope][$key] = $value; }
    public static function delete(string|int $key): void { unset(self::$values[self::$scope][$key]); }
    public static function list(): array { return array_keys(self::$values[self::$scope] ?? []); }
    public static function clear(): void { self::$values[self::$scope] = []; }
}

final class PublicOwnerIO implements BuilderIOInterface
{
    public Signal $signal;
    public function read(): array { return []; }
    public function write($data): bool { return true; }
    public function setSignal(Signal $signal): void { $this->signal = $signal; }
}
