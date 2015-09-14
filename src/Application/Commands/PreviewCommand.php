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

use Exception;
use Symfony\Component\Console\Command\Command;
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
 */
class PreviewCommand extends Command
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
		throw new Exception('TODO');
	}

	/**
	 * @SlotFor(Maslosoft\Sitcom\Command)
	 * @param Maslosoft\Signals\Command $signal
	 */
	public function reactOn(\Maslosoft\Sitcom\Command $signal)
	{
		$signal->add($this, 'hedron');
	}

}
