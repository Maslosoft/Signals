<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\Signals\Application;

use Maslosoft\Signals\Commands\BuildCommand;
use Maslosoft\Signals\Commands\PreviewCommand;
use Maslosoft\Signals\Signal;
use Symfony\Component\Console\Application as ConsoleApplication;

/**
 * SignalsApplication
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class Application extends ConsoleApplication
{

	const Logo = <<<LOGO
   _____ _                   __
  / ___/(_)___ _____  ____ _/ /____
  \__ \/ / __ `/ __ \/ __ `/ / ___/
 ___/ / / /_/ / / / / /_/ / (__  )
/____/_/\__, /_/ /_/\__,_/_/____/
       /____/

LOGO;

	public function __construct()
	{
		parent::__construct('Signals', (new Signal)->getVersion());
		$this->add(new BuildCommand());
		$this->add(new PreviewCommand());
	}

	public function getHelp()
	{
		return self::Logo . parent::getHelp();
	}

}
