<?php

/**
 * Wireless Cross-Component Communication
 *
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
use Maslosoft\Signals\Helpers\Preview;
use Maslosoft\Signals\Signal;
use Maslosoft\Sitcom\Command;
use Symfony\Component\Console\Command\Command as ConsoleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * PreviewCommand
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */

/**
 * PreviewCommand
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 * @codeCoverageIgnore
 */
class PreviewCommand extends ConsoleCommand implements AnnotatedInterface
{

	protected function configure(): void
	{
		$this->setName("preview");
		$this->setDescription("Show list of signals and slots");
		$this->setDefinition([
		]);

		$help = <<<EOT
The <info>preview</info> command will display list of signals and slots.
No files will be modified at this stage.
Use -vv option to also get list of processed files.
EOT;
		$this->setHelp($help);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$preview = new Preview();
		$signal = new Signal;

		$output->writeln("Scanning sources of... ");
		$paths = [];
		foreach ($signal->paths as $path)
		{
			$paths[] = '<info>' . realpath($path) . '</info>';
		}
		$output->writeln(implode("\n", $paths));

		$lines = [];
		foreach ($preview->cli($signal) as $line)
		{
			if (!strstr($line, "\t"))
			{
				$lines[] = "<info>$line</info>";
			}
			else
			{
				$lines[] = $line;
			}
		}

		$output->writeln('Processed files:', OutputInterface::VERBOSITY_VERY_VERBOSE);
		$output->writeln(implode("\n", $preview->getPaths()), OutputInterface::VERBOSITY_VERY_VERBOSE);

		$output->writeln("Following signals and slots was found:");
		foreach ($lines as $line)
		{
			$output->writeln($line);
		}
	}

	/**
	 * @SlotFor(Command)
	 * @param Command $signal
	 */
	public function reactOn(Command $signal): void
	{
		$signal->add($this, 'signals');
	}

}
