<?php

/**
 * This software package is licensed under `AGPL, Commercial` license[s].
 *
 * @package maslosoft/signals
 * @license AGPL, Commercial
 *
 * @copyright Copyright (c) Peter Maselkowski <pmaselkowski@gmail.com>
 *
 */

namespace Maslosoft\Signals\Application\Commands;

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
class PreviewCommand extends ConsoleCommand
{

	protected function configure()
	{
		$this->setName("preview");
		$this->setDescription("Show list of signals and slots");
		$this->setDefinition([
		]);

		$help = <<<EOT
The <info>preview</info> command will display list of signals and slots.
				No files will be modified at this stage.
EOT;
		$this->setHelp($help);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$preview = new Preview();
		$signal = new Signal;

		$output->writeln("Scanning sources of... ");
		$paths = [];
		foreach($signal->paths as $path)
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
		$output->writeln("Following signals and slots was found:");
		foreach ($lines as $line)
		{
			$output->writeln($line);
		}
	}

	/**
	 * @SlotFor(Maslosoft\Sitcom\Command)
	 * @param Command $signal
	 */
	public function reactOn(Command $signal)
	{
		$signal->add($this, 'signals');
	}

}
