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

namespace Maslosoft\Signals\Application\Commands;

use Maslosoft\Addendum\Interfaces\AnnotatedInterface;
use Maslosoft\Cli\Shared\Log\Logger;
use Maslosoft\Signals\Signal;
use Maslosoft\Signals\Utility;
use Maslosoft\Sitcom\Command;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Command\Command as ConsoleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * SignalsCommand
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 * @codeCoverageIgnore
 */
class BuildCommand extends ConsoleCommand implements AnnotatedInterface
{

	protected function configure()
	{
		$this->setName("build");
		$this->setDescription("Build signals list");
		$this->setDefinition([
		]);

		$help = <<<EOT
The <info>build</info> command will scan files for signals and save them to file.
EOT;
		$this->setHelp($help);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$signal = new Signal();

		// Set default logger if not configured
		$currentLogger = $signal->getLogger();
		if ($currentLogger instanceof NullLogger)
		{
			$signal->setLogger(new Logger($output));
		}

		(new Utility($signal))->generate();
	}

	/**
	 * @SlotFor(Command)
	 * @param Command $signal
	 */
	public function reactOn(Command $signal)
	{
		$signal->add($this, 'signals');
	}

}
