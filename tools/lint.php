<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$files = [$root . '/signals'];
foreach (['src', 'tests', 'examples', 'docs', 'tools'] as $directory) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
        $root . '/' . $directory, FilesystemIterator::SKIP_DOTS
    ));
    foreach ($iterator as $file) {
        if ($file->getExtension() === 'php'
            && !preg_match('~/(?:_output|_generated|runtime)/~', $file->getPathname())) {
            $files[] = $file->getPathname();
        }
    }
}
sort($files);
foreach ($files as $file) {
    $process = proc_open([PHP_BINARY, '-d', 'error_reporting=-1', '-l', $file],
        [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if (!is_resource($process)) {
        throw new RuntimeException('Could not start PHP lint.');
    }
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    if (proc_close($process) !== 0 || $stderr !== '') {
        fwrite(STDERR, $stdout . $stderr);
        exit(1);
    }
}
printf("Linted %d PHP files.\n", count($files));
