<?php

declare(strict_types=1);

namespace Application;

use Codeception\Test\Unit;

final class CliTest extends Unit
{
    public function testBuildPreviewAndErrorsHaveUsefulExitStatuses(): void
    {
        $directory = sys_get_temp_dir() . '/signals-cli-' . bin2hex(random_bytes(8));
        mkdir($directory);
        file_put_contents($directory . '/signals.yml', "paths: []\n");
        try {
            [$status, $output] = $this->runCommand($directory, 'preview');
            self::assertSame(0, $status, $output);
            self::assertDirectoryDoesNotExist($directory . '/runtime');
            self::assertDirectoryDoesNotExist($directory . '/generated');

            [$status, $output] = $this->runCommand($directory, 'build');
            self::assertSame(0, $status, $output);
            self::assertFileExists($directory . '/generated/signals-definition.php');
            self::assertFileExists($directory . '/runtime/signals.php');
            $definitions = require $directory . '/generated/signals-definition.php';
            self::assertEquals(['signals' => [], 'slots' => []], $definitions);

            [$status, $output] = $this->runCommand($directory, 'preview');
            self::assertSame(0, $status, $output);
            self::assertStringContainsString('Signals:', $output);
            self::assertStringContainsString('Slots:', $output);

            [$status] = $this->runCommand($directory, 'missing-command');
            self::assertNotSame(0, $status);

            file_put_contents($directory . '/blocked', 'This is a file.');
            file_put_contents($directory . '/signals.yml', "paths: []\nio:\n  class: Maslosoft\\Signals\\Builder\\IO\\PhpFile\n  generatedPath: blocked/subdirectory\n");
            [$status] = $this->runCommand($directory, 'build');
            self::assertNotSame(0, $status);
        } finally {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(
                $directory, \FilesystemIterator::SKIP_DOTS
            ), \RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $file) {
                $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
            }
            rmdir($directory);
        }
    }

    private function runCommand(string $directory, string $command): array
    {
        $process = proc_open([PHP_BINARY, '-d', 'register_argc_argv=1',
            dirname(__DIR__, 3) . '/signals', $command, '--no-ansi'],
            [1 => ['pipe', 'w'], 2 => ['redirect', 1]], $pipes, $directory);
        self::assertIsResource($process);
        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        return [proc_close($process), $output];
    }
}
