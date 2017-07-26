<?php

use Maslosoft\Signals\Builder\IO\PhpFile;
use Maslosoft\Signals\Signal;
use Maslosoft\Zamm\ShortNamer;
use Maslosoft\Zamm\Widgets\PropertiesDocs;

?>
<?php
ShortNamer::defaults()->md();
$name = new ShortNamer(PhpFile::class);
?>
<title>1. PHP IO</title>

# PHP File Input Output Interface

In most cases storing signals definition in PHP file is sufficient and
performs well, as opcode cache will keep this file in memory. This file
can also be kept in VCS repository, so that when application is deployed,
in production environment there is no need for code scanning.

## Configurable options for <?= $name; ?>

<?= new PropertiesDocs(PhpFile::class); ?>
