<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\Signals\Commands;

use Maslosoft\Signals\Signal;
use Maslosoft\Signals\Utility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * SignalsCommand
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class BuildCommand extends Command
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
	 * @param Maslosoft\Signals\Command $signal
	 */
	public function reactOn(\Maslosoft\Sitcom\Command $signal)
	{
		$signal->add($this, 'signals');
	}
}
