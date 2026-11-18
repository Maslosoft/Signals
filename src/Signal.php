<?php

declare(strict_types=1);

/**
 * Wireless Cross-Component Communication
 * @package maslosoft/signals
 * @license AGPL-3.0-only, proprietary
 * @copyright Copyright (c) Peter Maselkowski <pmaselkowski@gmail.com>
 */

namespace Maslosoft\Signals;

use InvalidArgumentException;
use Maslosoft\Addendum\Utilities\NameNormalizer;
use Maslosoft\Signals\Helpers\Configuration;
use Maslosoft\EmbeDi\EmbeDi;
use Maslosoft\Light\Context;
use Maslosoft\Signals\Builder\Addendum;
use Maslosoft\Signals\Builder\IO\PhpFile;
use Maslosoft\Signals\Factories\FilterFactory;
use Maslosoft\Signals\Factories\SlotFactory;
use Maslosoft\Signals\Helpers\PostFilter;
use Maslosoft\Signals\Helpers\PreFilter;
use Maslosoft\Signals\Interfaces\BuilderIOInterface;
use Maslosoft\Signals\Interfaces\ExtractorInterface;
use Maslosoft\Signals\Interfaces\FilterInterface;
use Maslosoft\Signals\Interfaces\PostFilterInterface;
use Maslosoft\Signals\Interfaces\PreFilterInterface;
use Maslosoft\Signals\Interfaces\SignalAwareInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use UnexpectedValueException;

/**
 * Dispatch signals using definitions owned by this instance.
 * @property LoggerInterface $logger
 * @phpstan-consistent-constructor
 */
class Signal implements LoggerAwareInterface
{
    public const string Slots = 'slots';
    public const string Signals = 'signals';
    public const string ConfigFilename = 'signals-definition.php';
    public const string ConfigName = 'signals';

    private const string ContextKey = 'maslosoft.signals.instances';

    public string $runtimePath = 'runtime';

    /** @var list<string> Paths searched for SlotFor and SignalFor annotations. */
    public array $paths = ['vendor'];

    /** @var list<string> Directory names excluded during scanning. */
    public array $ignoreDirs = ['vendor', 'generated', 'runtime'];

    /** @var array<array|string|FilterInterface> Filters applied to every dispatch. */
    public array $filters = [];

    /** @var array Kept for configuration compatibility; sorting is not implemented. */
    public array $sorters = [];

    public string|array|ExtractorInterface $extractor = Addendum::class;
    public string|array|BuilderIOInterface $io = PhpFile::class;

    private bool $isInitialized = false;
    private array $config = [];
    private array $configs = [];
    /** @var array<string, array{definition: string|array, instance: BuilderIOInterface|ExtractorInterface}> */
    private array $configured = [];
    private LoggerInterface $loggerInstance;
    private EmbeDi $di;
    private ?string $version = null;

    /** @var list<PreFilterInterface|PostFilterInterface> Filters for the next dispatch. */
    private array $currentFilters = [];

    /** @var list<list<PreFilterInterface|PostFilterInterface>> Nested dispatch frames. */
    private array $filterStack = [];

    public function __construct(string $configName = self::ConfigName)
    {
        $this->loggerInstance = new NullLogger();
        $this->di = EmbeDi::fly($configName);
        $this->di->configure($this);
        $this->di->apply(Configuration::read($configName, $this->runtimePath), $this);
    }

    /** Reuse one dispatcher per class and configuration name in the current Light context. */
    public static function fly(string $configName = self::ConfigName): static
    {
        $instances = Context::get(self::ContextKey) ?? [];
        if (!isset($instances[static::class][$configName])) {
            $instance = new static($configName);
            // Construction may configure other dispatchers; retain those entries.
            $instances = Context::get(self::ContextKey) ?? [];
            $instances[static::class][$configName] = $instance;
            Context::put(self::ContextKey, $instances);
        }
        return $instances[static::class][$configName];
    }

    /** Forget Signals flyweights in this context, preserving other Light users. */
    public static function reset(): void
    {
        Context::delete(self::ContextKey);
    }

    public function __get(string $name): mixed
    {
        $method = 'get' . ucfirst($name);
        if (!method_exists($this, $method)) {
            throw new InvalidArgumentException(sprintf('Unknown Signals property `%s`.', $name));
        }
        return $this->$method();
    }

    public function __set(string $name, mixed $value): void
    {
        $method = 'set' . ucfirst($name);
        if (!method_exists($this, $method)) {
            throw new InvalidArgumentException(sprintf('Unknown or read-only Signals property `%s`.', $name));
        }
        $this->$method($value);
    }

    public function getVersion(): string
    {
        return $this->version ??= require __DIR__ . '/version.php';
    }

    public function init(): void
    {
        if (!$this->isInitialized) {
            $this->reload();
        }
        if (!$this->di->isStored($this)) {
            $this->di->store($this);
        }
    }

    /** Attach an overlay to this instance; later overlays replace matching definitions. */
    public function attach(array $config, bool $reload = true): void
    {
        $this->configs[] = $config;
        if ($reload) {
            $this->reload();
        }
    }

    /** Queue a filter for the next emit/gather; false clears only queued filters. */
    public function filter(array|string|FilterInterface|false $filter): static
    {
        if ($filter === false) {
            $this->currentFilters = [];
            return $this;
        }
        if (!is_object($filter)) {
            $filter = $this->di->apply($filter);
        }
        if (!$filter instanceof PreFilterInterface && !$filter instanceof PostFilterInterface) {
            throw new UnexpectedValueException(sprintf('Filter must implement %s or %s.', PreFilterInterface::class, PostFilterInterface::class));
        }
        $this->currentFilters[] = $filter;
        return $this;
    }

    /** @return list<object> A separate signal clone for every successful injection. */
    public function emit(object|string $signal): array
    {
        $this->beginDispatch();
        try {
            if (is_string($signal)) {
                $signal = new $signal();
            }
            $name = $signal::class;
            NameNormalizer::normalize($name);
            $this->init();
            if (!isset($this->config[self::Signals][$name])) {
                $this->loggerInstance->debug('No slots found for signal `{name}`, skipping', ['name' => $name]);
                return [];
            }
            $result = [];
            foreach ($this->config[self::Signals][$name] as $fqn => $injections) {
                if ($injections === false || $injections === []) {
                    continue;
                }
                if (!PreFilter::filter($this, $fqn, $signal)) {
                    continue;
                }
                foreach ($injections as $injection) {
                    $injected = SlotFactory::create($this, $signal, $fqn, $injection);
                    if ($injected !== false && PostFilter::filter($this, $injected, $signal)) {
                        $result[] = $injected;
                    }
                }
            }
            return $result;
        } finally {
            $this->endDispatch();
        }
    }

    /** @return list<object> Providers registered for the slot, optionally restricted by type. */
    public function gather(object $slot, ?string $interface = null): array
    {
        $this->beginDispatch();
        try {
            $name = $slot::class;
            NameNormalizer::normalize($name);
            $this->init();
            if (!isset($this->config[self::Slots][$name])) {
                $this->loggerInstance->debug('No signals found for slot `{name}`, skipping', ['name' => $name]);
                return [];
            }
            $result = [];
            foreach ($this->config[self::Slots][$name] as $fqn => $emit) {
                if ($emit === false || !PreFilter::filter($this, $fqn, $slot)) {
                    continue;
                }
                if (!class_exists($fqn)) {
                    $this->loggerInstance->debug('Class `{class}` not found while gathering slot `{slot}`', ['class' => $fqn, 'slot' => $name]);
                    continue;
                }
                if ($interface !== null && !is_a($fqn, $interface, true)) {
                    continue;
                }
                $injected = new $fqn();
                if (PostFilter::filter($this, $injected, $slot)) {
                    $result[] = $injected;
                }
            }
            return $result;
        } finally {
            $this->endDispatch();
        }
    }

    /** @return list<PreFilterInterface|PostFilterInterface> */
    public function getFilters(string $interface): array
    {
        $filters = FilterFactory::create($this, $interface);
        $temporary = $this->filterStack === [] ? $this->currentFilters : $this->filterStack[array_key_last($this->filterStack)];
        foreach ($temporary as $filter) {
            if ($filter instanceof $interface) {
                $filters[] = $filter;
            }
        }
        return $filters;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->loggerInstance;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->loggerInstance = $logger;
    }

    public function getDi(): EmbeDi
    {
        return $this->di;
    }

    public function setDi(EmbeDi $di): static
    {
        $this->di = $di;
        return $this;
    }

    public function getIO(): BuilderIOInterface
    {
        return $this->getConfigured('io');
    }

    public function setIO(BuilderIOInterface $io): static
    {
        $this->isInitialized = false;
        return $this->setConfigured($io, 'io');
    }

    public function getExtractor(): ExtractorInterface
    {
        return $this->getConfigured('extractor');
    }

    public function setExtractor(ExtractorInterface $extractor): static
    {
        return $this->setConfigured($extractor, 'extractor');
    }

    /** Reload this instance's definitions, retaining attached overlays. */
    public function resetCache(): void
    {
        $this->reload();
    }

    private function reload(): void
    {
        $config = $this->getIO()->read();
        foreach ($this->configs as $overlay) {
            $config = array_replace_recursive($config, $overlay);
        }
        $this->config = $config;
        $this->isInitialized = true;
    }

    private function getConfigured(string $property): BuilderIOInterface|ExtractorInterface
    {
        $definition = $this->$property;
        if (is_object($definition)) {
            $object = $definition;
        } elseif (isset($this->configured[$property]) && $this->configured[$property]['definition'] === $definition) {
            $object = $this->configured[$property]['instance'];
        } else {
            $object = $this->di->apply($definition);
        }
        $interface = $property === 'io' ? BuilderIOInterface::class : ExtractorInterface::class;
        if (!$object instanceof $interface) {
            throw new UnexpectedValueException(sprintf('%s must implement %s.', $property, $interface));
        }
        $object->setSignal($this);
        if (!is_object($definition)) {
            $this->configured[$property] = ['definition' => $definition, 'instance' => $object];
        }
        return $object;
    }

    private function setConfigured(SignalAwareInterface $object, string $property): static
    {
        $object->setSignal($this);
        $this->$property = $object;
        unset($this->configured[$property]);
        return $this;
    }

    private function beginDispatch(): void
    {
        $this->filterStack[] = $this->currentFilters;
        $this->currentFilters = [];
    }

    private function endDispatch(): void
    {
        array_pop($this->filterStack);
        // A queued filter created by a failed/finished handler must not leak into another request.
        $this->currentFilters = [];
    }
}
