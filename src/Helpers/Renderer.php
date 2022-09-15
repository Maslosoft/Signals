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

namespace Maslosoft\Signals\Helpers;

use Maslosoft\Signals\Signal;

/**
 * Renderer
 * @codeCoverageIgnore
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class Renderer
{

	public function renderCli($data)
	{
		$result = [];
		foreach ([Signal::Signals => 'Signals:', Signal::Slots => 'Slots:'] as $type => $name)
		{
			$result[] = "$name";
			$result[] = "";
			foreach ($data[$type] as $signal => $slots)
			{
				$result[] = $signal;
				foreach ($slots as $slot => $types)
				{
					foreach ((array) $types as $type)
					{
						if ($type === true)
						{
							$result[] = sprintf("\t%s", $slot);
						}
						else
						{
							$result[] = sprintf("\t%s::%s", $slot, $type);
						}
					}
				}
			}
		}
		return $result;
	}

}
