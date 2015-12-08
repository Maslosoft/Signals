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

use Maslosoft\Signals\Signal;
use Maslosoft\Signals\Utility;
use Maslosoft\Sitcom\Command;
use Symfony\Component\Console\Command\Command as ConsoleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * SignalsCommand
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 * @codeCoverageIgnore
 */
class BuildCommand extends ConsoleCommand
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
		(new Utility(new Signal()))->generate();
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
