<?php

/**
 * This software package is licensed under `AGPL-3.0-only, proprietary` license[s].
 *
 * @package maslosoft/signals
 * @license AGPL-3.0-only, proprietary
 *
 * @copyright Copyright (c) Peter Maselkowski <pmaselkowski@gmail.com>
 * @link https://maslosoft.com/signals/
 */

namespace Maslosoft\Signals;

use function is_object;
use Maslosoft\Addendum\Utilities\ClassChecker;
use Maslosoft\Addendum\Utilities\NameNormalizer;
use Maslosoft\Cli\Shared\ConfigReader;
use Maslosoft\EmbeDi\EmbeDi;
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
use ReflectionClass;
use UnexpectedValueException;

/**
 * Main signals components
 *
 * @author Piotr
 * @property LoggerInterface $logger Logger, set this to log warnings, notices errors. This is shorthand for `get/setLogger`.
 */
class Signal implements LoggerAwareInterface
{

	const Slots = 'slots';
	const Signals = 'signals';

	/**
	 * Generated signals name.
	 * Name of this constant is confusing.
	 * @internal description
	 */
	const ConfigFilename = 'signals-definition.php';

	/**
	 * Config file name
	 */
	const ConfigName = "signals";

	/**
	 * Runtime path is directory where config cache from yml file will
	 * be stored. Path is relative to project root. This must be writable
	 * by command line user.
	 *
	 * @var string
	 */
	public $runtimePath = 'runtime';

	/**
	 * This paths will be searched for `SlotFor` and `SignalFor` annotations.
	 *
	 *
	 *
	 * TODO Autodetect based on composer autoload
	 *
	 * @var string[]
	 */
	public $paths = [
		'vendor',
	];

	/**
	 * Directories to ignore while scanning
	 * @var string[]
	 */
	public $ignoreDirs = [
		'vendor', // Vendors in vendors
		'generated', // Generated data, including signals
		'runtime', // Runtime data
	];

	/**
	 * Filters configuration.
	 * This filters will be applied to every emit. This property
	 * should contain array of class names implementing filters.
	 * @var string[]|object[]
	 */
	public $filters = [];

	/**
	 * Sorters configuration.
	 * @var string[]|object[]
	 */
	public $sorters = [];

	/**
	 * Extractor configuration
	 * @var string|[]|object
	 */
	public $extractor = Addendum::class;

	/**
	 * Input/Output configuration, at minimum it should
	 * contain class name for builder input output interface.
	 * It can also contain array [configurable options for IO class](php-io/).
	 *
	 *
	 *
	 * @var string|[]|object
	 */
	public $io = PhpFile::class;

	/**
	 * Whenever component is initialized
	 * @var bool
	 */
	private $isInitialized = false;

	/**
	 * Configuration of signals and slots
	 * @var string[][]
	 */
	private static $config = [];

	/**
	 * Extra configurations of signals and slots
	 * @var array
	 */
	private static $configs = [];

	/**
	 * Logger instance holder
	 * NOTE: There is property annotation with `logger` name,
	 * thus this name is a bit longer
	 * @var LoggerInterface
	 */
	private $loggerInstance = null;

	/**
	 * Embedded dependency injection
	 * @var EmbeDi
	 */
	private $di = null;

	/**
	 * Version
	 * @var string
	 */
	private $version = null;

	/**
	 * Current filters
	 * @var PreFilterInterface[]|PostFilterInterface[]
	 */
	private $currentFilters = [];

	public function __construct($configName = self::ConfigName)
	{
		$this->loggerInstance = new NullLogger;

		$config = new ConfigReader($configName);
		$this->di = EmbeDi::fly($configName);
		$this->di->configure($this);
		$this->di->apply($config->toArray(), $this);
	}

	/**
	 * Getter
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		return $this->{'get' . ucfirst($name)}();
	}

	/**
	 * Setter
	 * @param string $name
	 * @param mixed $value
	 * @return mixed
	 */
	public function __set($name, $value)
	{
		return $this->{'set' . ucfirst($name)}($value);
	}

	/**
	 * Get current signals version
	 *
	 * @codeCoverageIgnore
	 * @return string
	 */
	public function getVersion()
	{
		if (null === $this->version)
		{
			$this->version = require __DIR__ . '/version.php';
		}
		return $this->version;
	}

	public function init()
	{
		if (!$this->isInitialized)
		{
			$this->reload();
		}
		if (!$this->di->isStored($this))
		{
			$this->di->store($this);
		}
	}

	/**
	 * Attach additional signals and slots configuration
	 * @param      $config
	 * @param bool $reload
	 */
	public function attach($config, $reload = true)
	{
		self::$configs[] = $config;
		if($reload)
		{
			$this->reload();
		}
	}

	/**
	 * Apply filter to current emit.
	 *
	 * Pass false as param to disable all filters.
	 *
	 * @param FilterInterface|string|mixed $filter
	 * @return Signal
	 * @throws UnexpectedValueException
	 */
	public function filter($filter)
	{
		// disable filters
		if (is_bool($filter) && false === $filter)
		{
			$this->currentFilters = [];
			return $this;
		}
		// Instantiate from string or array
		if (!is_object($filter))
		{
			$filter = $this->di->apply($filter);
		}
		if (!$filter instanceof PreFilterInterface && !$filter instanceof PostFilterInterface)
		{
			throw new UnexpectedValueException(sprintf('$filter must implement either `%s` or `%s` interface', PreFilterInterface::class, PostFilterInterface::class));
		}
		$this->currentFilters[] = $filter;
		return $this;
	}

	/**
	 * Emit signal to inform slots
	 * @param object|string $signal
	 * @return object[]
	 */
	public function emit($signal)
	{
		$result = [];
		if (is_string($signal))
		{
			$signal = new $signal;
		}
		$name = get_class($signal);
		NameNormalizer::normalize($name);
		if (empty(self::$config))
		{
			$this->init();
		}
		if (!isset(self::$config[self::Signals][$name]))
		{
			self::$config[self::Signals][$name] = [];
			$this->loggerInstance->debug('No slots found for signal `{name}`, skipping', ['name' => $name]);
			return $result;
		}

		foreach (self::$config[self::Signals][$name] as $fqn => $injections)
		{
			// Skip
			if (false === $injections || count($injections) == 0)
			{
				continue;
			}
			if (!PreFilter::filter($this, $fqn, $signal))
			{
				continue;
			}
			foreach ($injections as $injection)
			{
				$injected = SlotFactory::create($this, $signal, $fqn, $injection);
				if (false === $injected)
				{
					continue;
				}
				if (!PostFilter::filter($this, $injected, $signal))
				{
					continue;
				}
				$result[] = $injected;
			}
		}
		$this->currentFilters = [];
		return $result;
	}

	/**
	 * Call for signals from slot
	 * @param object $slot
	 * @param string $interface Interface or class name which must be implemented, instanceof or sub class of to get
	 *                          into slot
	 * @return array
	 */
	public function gather($slot, $interface = null)
	{
		assert(is_object($slot), 'Parameter `$slot` must be object');
		$name = get_class($slot);
		NameNormalizer::normalize($name);
		if (!empty($interface))
		{
			NameNormalizer::normalize($interface);
		}
		if (empty(self::$config))
		{
			$this->init();
		}
		if (!isset(self::$config[self::Slots][$name]))
		{
			self::$config[self::Slots][$name] = [];
			$this->loggerInstance->debug('No signals found for slot `{name}`, skipping', ['name' => $name]);
		}
		$result = [];
		foreach ((array) self::$config[self::Slots][$name] as $fqn => $emit)
		{
			if (false === $emit)
			{
				continue;
			}
			if (!PreFilter::filter($this, $fqn, $slot))
			{
				continue;
			}
			// Check if class exists and log if doesn't
			if (!ClassChecker::exists($fqn))
			{
				$this->loggerInstance->debug(sprintf("Class `%s` not found while gathering slot `%s`", $fqn, get_class($slot)));
				continue;
			}
			if (null === $interface)
			{
				$injected = new $fqn;
				if (!PostFilter::filter($this, $injected, $slot))
				{
					continue;
				}
				$result[] = $injected;
				continue;
			}

			// Check if it's same as interface
			if ($fqn === $interface)
			{
				$injected = new $fqn;
				if (!PostFilter::filter($this, $injected, $slot))
				{
					continue;
				}
				$result[] = $injected;
				continue;
			}

			$info = new ReflectionClass($fqn);

			// Check if class is instance of base class
			if ($info->isSubclassOf($interface))
			{
				$injected = new $fqn;
				if (!PostFilter::filter($this, $injected, $slot))
				{
					continue;
				}
				$result[] = $injected;
				continue;
			}

			$interfaceInfo = new ReflectionClass($interface);
			// Check if class implements interface
			if ($interfaceInfo->isInterface() && $info->implementsInterface($interface))
			{
				$injected = new $fqn;
				if (!PostFilter::filter($this, $injected, $slot))
				{
					continue;
				}
				$result[] = $injected;
				continue;
			}
		}
		return $result;
	}

	/**
	 * Get filters
	 * @param string $interface
	 * @return PreFilterInterface[]|PostFilterInterface[]
	 */
	public function getFilters($interface)
	{
		$filters = FilterFactory::create($this, $interface);
		foreach ($this->currentFilters as $filter)
		{
			if (!$filter instanceof $interface)
			{
				continue;
			}
			$filters[] = $filter;
		}
		return $filters;
	}

	/**
	 * Get logger
	 * @codeCoverageIgnore
	 * @return LoggerInterface
	 */
	public function getLogger()
	{
		return $this->loggerInstance;
	}

	/**
	 * Set logger
	 * @codeCoverageIgnore
	 * @param LoggerInterface $logger
	 * @return Signal
	 */
	public function setLogger(LoggerInterface $logger)
	{
		$this->loggerInstance = $logger;
		return $this;
	}

	/**
	 * Get dependency injection container
	 * @return EmbeDi
	 */
	public function getDi()
	{
		return $this->di;
	}

	public function setDi(EmbeDi $di)
	{
		$this->di = $di;
		return $this;
	}

	/**
	 * Get Input/Output adapter
	 * @codeCoverageIgnore
	 * @return BuilderIOInterface I/O Adapter
	 */
	public function getIO()
	{
		return $this->getConfigured('io');
	}

	/**
	 * Set Input/Output interface
	 * @codeCoverageIgnore
	 * @param BuilderIOInterface $io
	 * @return Signal
	 */
	public function setIO(BuilderIOInterface $io)
	{
		return $this->setConfigured($io, 'io');
	}

	/**
	 * @codeCoverageIgnore
	 * @return ExtractorInterface
	 */
	public function getExtractor()
	{
		return $this->getConfigured('extractor');
	}

	/**
	 * @codeCoverageIgnore
	 * @param ExtractorInterface $extractor
	 * @return Signal
	 */
	public function setExtractor(ExtractorInterface $extractor)
	{
		return $this->setConfigured($extractor, 'extractor');
	}

	/**
	 * Reloads signals cache and re-initializes component.
	 */
	public function resetCache()
	{
		$this->reload();
	}

	private function reload()
	{
		self::$config = $this->getIO()->read();

		foreach(self::$configs as $config)
		{
			self::$config = array_replace_recursive(self::$config, $config);
		}
	}

	/**
	 * Get configured property
	 * @param string $property
	 * @return SignalAwareInterface|ExtractorInterface|BuilderIOInterface
	 */
	private function getConfigured($property)
	{
		if (is_object($this->$property))
		{
			$object = $this->$property;
		}
		else
		{
			$object = $this->di->apply($this->$property);
		}
		if ($object instanceof SignalAwareInterface)
		{
			$object->setSignal($this);
		}
		return $object;
	}

	/**
	 * Set signal aware property
	 * @param SignalAwareInterface $object
	 * @param string $property
	 * @return Signal
	 */
	private function setConfigured(SignalAwareInterface $object, $property)
	{
		$object->setSignal($this);
		$this->$property = $object;
		return $this;
	}

}
