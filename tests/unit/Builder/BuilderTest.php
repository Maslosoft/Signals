<?php

declare(strict_types=1);

namespace Builder;

use Codeception\Test\Unit;
use Maslosoft\Signals\Builder\Addendum;
use Maslosoft\Signals\Builder\IO\Memory;
use Maslosoft\Signals\Builder\IO\PhpFile;
use Maslosoft\Signals\Signal;
use RuntimeException;
use UnexpectedValueException;

final class BuilderTest extends Unit
{
    private string $directory;

    protected function _before(): void
    {
        $this->directory = sys_get_temp_dir() . '/signals-builder-' . bin2hex(random_bytes(8));
        mkdir($this->directory);
    }

    protected function _after(): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($this->directory);
    }

    public function testMemoryIsEmptyBeforeFirstWriteAndRetainsDefinitions(): void
    {
        $io = new Memory();
        self::assertSame([], $io->read());
        $data = [Signal::Signals => ['\\A' => false], Signal::Slots => []];
        self::assertTrue($io->write($data));
        self::assertSame($data, $io->read());
    }

    public function testPhpFileCreatesDirectoriesAndReplacesCompleteSnapshots(): void
    {
        $io = $this->fileIO();
        $io->configFilename = 'nested/definitions.php';
        self::assertSame([], $io->read());
        $first = [Signal::Signals => ["\\Name\\With'Quote" => false], Signal::Slots => []];
        self::assertTrue($io->write($first));
        self::assertSame($first, $io->read());
        $path = $this->directory . '/nested/definitions.php';
        chmod($path, 0640);
        $second = [Signal::Signals => [], Signal::Slots => ['\\Slot' => ['\\Signal' => true]]];
        self::assertTrue($io->write($second));
        self::assertSame($second, $io->read());
        clearstatcache(true, $path);
        self::assertSame(0640, fileperms($path) & 0777);
        self::assertSame([], glob($this->directory . '/nested/.signals-*'));
    }

    public function testPhpFileRejectsMalformedSnapshot(): void
    {
        $io = $this->fileIO();
        file_put_contents($this->directory . '/' . $io->configFilename, '<?php return true;');
        $this->expectException(UnexpectedValueException::class);
        $io->read();
    }

    public function testPhpFileCleansTemporaryFileWhenReplacementFails(): void
    {
        $io = $this->fileIO();
        mkdir($this->directory . '/' . $io->configFilename);
        try {
            $io->write([]);
            self::fail('Replacing a directory must fail.');
        } catch (RuntimeException $exception) {
            self::assertStringContainsString('Cannot replace', $exception->getMessage());
        }
        self::assertSame([], glob($this->directory . '/.signals-*'));
        self::assertDirectoryExists($this->directory . '/' . $io->configFilename);
    }

    public function testRepeatedExtractionDropsDefinitionsFromPreviousPaths(): void
    {
        $signal = new Signal('signals-builder-test');
        $signal->paths = [MODELS_PATH];
        $extractor = new Addendum();
        $extractor->setSignal($signal);
        self::assertNotEmpty($extractor->getData()[Signal::Signals]);
        self::assertNotEmpty($extractor->getPaths());
        $signal->paths = [];
        self::assertEquals([Signal::Signals => [], Signal::Slots => []], $extractor->getData());
        self::assertSame([], $extractor->getPaths());
    }

    public function testUnresolvableClassDoesNotChangeAutoloadersOrErrorReporting(): void
    {
        $class = 'UnresolvableBuildClass' . bin2hex(random_bytes(8));
        $file = $this->directory . '/' . $class . '.php';
        $contents = '<?php class ' . $class . ' {}';
        file_put_contents($file, $contents);
        $extractor = new Addendum();
        $extractor->setSignal(new Signal('signals-builder-test'));
        $autoloaders = spl_autoload_functions();
        $reporting = error_reporting();
        $extractor->processFile($file, $contents);
        self::assertSame($autoloaders, spl_autoload_functions());
        self::assertSame($reporting, error_reporting());
    }

    public function testInvalidAnnotationDoesNotChangeErrorReporting(): void
    {
        $class = 'InvalidBuildAnnotation' . bin2hex(random_bytes(8));
        $file = $this->directory . '/' . $class . '.php';
        $contents = '<?php /** @SlotFor(\\UnknownSignalsBuildClass) */ class ' . $class
            . ' implements \\Maslosoft\\Addendum\\Interfaces\\AnnotatedInterface {}';
        file_put_contents($file, $contents);
        require $file;
        $extractor = new Addendum();
        $extractor->setSignal(new Signal('signals-builder-test'));
        $reporting = error_reporting();
        try {
            $extractor->processFile($file, $contents);
            self::assertSame($reporting, error_reporting());
        } finally {
            error_reporting($reporting);
        }
    }

    private function fileIO(): PhpFile
    {
        $io = new PhpFile();
        $io->generatedPath = $this->directory;
        return $io;
    }
}
