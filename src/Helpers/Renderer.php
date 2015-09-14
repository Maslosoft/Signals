<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\Signals\Helpers;

use Maslosoft\Signals\Signal;

/**
 * Renderer
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class Renderer
{

	public function renderCli($data)
	{
		foreach ([Signal::Signals => 'Signals:', Signal::Slots => 'Slots:'] as $type => $name)
		{
			yield "$name";
			yield "";
			foreach ($data[$type] as $signal => $slots)
			{
				yield $signal;
				foreach ($slots as $slot => $types)
				{
					foreach ($types as $type)
					{
						if ($type === true)
						{
							yield sprintf("\t%s", $slot);
						}
						else
						{
							yield sprintf("\t%s::%s", $slot, $type);
						}
					}
				}
			}
		}
	}

}
