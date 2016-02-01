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
					foreach ((array) $types as $type)
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
