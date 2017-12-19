<?php

use Maslosoft\Signals\Signal;

// Here you can initialize variables that will be available to your tests

echo "Signals " . (new Signal())->getVersion();
