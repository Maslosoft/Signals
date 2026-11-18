<?php

declare(strict_types=1);

namespace Helpers;

use Codeception\Test\Unit;
use Maslosoft\EmbeDi\Adapters\ArrayAdapter;
use Maslosoft\EmbeDi\EmbeDi;
use Maslosoft\Signals\Helpers\Configuration;
use Maslosoft\Signals\Signal;
use Symfony\Component\Yaml\Exception\ParseException;
use UnexpectedValueException;

final class ConfigurationTest extends Unit
{
    private string $directory;
    private string $workingDirectory;
    private array $adapters;

    protected function _before(): void
    {
        $this->workingDirectory = getcwd();
        $this->directory = sys_get_temp_dir() . '/signals-configuration-' . bin2hex(random_bytes(8));
        mkdir($this->directory);
        chdir($this->directory);
        $this->adapters = EmbeDi::fly()->getAdapters();
    }

    protected function _after(): void
    {
        EmbeDi::fly()->setAdapters($this->adapters);
        chdir($this->workingDirectory);
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(
            $this->directory, \FilesystemIterator::SKIP_DOTS
        ), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($this->directory);
    }

    public function testMissingConfigurationDoesNotCreateRuntimeFiles(): void
    {
        self::assertSame([], Configuration::read('absent'));
        self::assertDirectoryDoesNotExist('runtime');
    }

    public function testReadingYamlAndConstructingSignalsDoNotWriteCache(): void
    {
        file_put_contents('sample.yml', "paths: [application]\n");
        self::assertSame(['paths' => ['application']], Configuration::read('sample'));
        $signal = new Signal('sample');
        self::assertSame(['application'], $signal->paths);
        self::assertDirectoryDoesNotExist('runtime');
    }

    public function testYamlTakesPrecedenceWithoutReplacingTheExistingCache(): void
    {
        Configuration::write('sample', 'runtime', ['paths' => ['cached']]);
        $cache = file_get_contents('runtime/sample.php');
        file_put_contents('sample.yml', "paths: [application]\n");
        self::assertSame(['paths' => ['application']], Configuration::read('sample'));
        self::assertSame($cache, file_get_contents('runtime/sample.php'));
        file_put_contents('sample.yml', '[]');
        self::assertSame(['paths' => ['cached']], Configuration::read('sample'));
        unlink('sample.yml');
        self::assertSame(['paths' => ['cached']], Configuration::read('sample'));
    }

    public function testExplicitPublicationUsesTheConfiguredRuntimePath(): void
    {
        $path = $this->directory . '/custom/cache';
        Configuration::write('sample', $path, ['paths' => ['custom']]);
        self::assertFileExists($path . '/sample.php');
        self::assertDirectoryDoesNotExist('runtime');
        self::assertSame(['paths' => ['custom']], Configuration::read('sample', $path));
    }

    public function testSignalsUsesEmbeddableRuntimePathBeforeReadingConfiguration(): void
    {
        $path = $this->directory . '/configured-cache';
        Configuration::write('configured', $path, ['paths' => ['from-cache']]);
        EmbeDi::fly()->addAdapter(new ArrayAdapter([
            'configured' => ['class' => Signal::class, 'runtimePath' => $path],
        ]));
        $signal = new Signal('configured');
        self::assertSame($path, $signal->runtimePath);
        self::assertSame(['from-cache'], $signal->paths);
    }

    public function testScalarYamlIsRejectedInsteadOfFallingBack(): void
    {
        file_put_contents('sample.yml', 'false');
        $this->expectException(UnexpectedValueException::class);
        Configuration::read('sample');
    }

    public function testMalformedYamlIsRejected(): void
    {
        file_put_contents('sample.yml', 'paths: [unclosed');
        $this->expectException(ParseException::class);
        Configuration::read('sample');
    }

    public function testScalarPhpCacheIsRejected(): void
    {
        mkdir('runtime');
        file_put_contents('runtime/sample.php', '<?php return false;');
        $this->expectException(UnexpectedValueException::class);
        Configuration::read('sample');
    }

    public function testMalformedPhpCacheIsRejected(): void
    {
        mkdir('runtime');
        file_put_contents('runtime/sample.php', '<?php syntax error');
        $this->expectException(\ParseError::class);
        Configuration::read('sample');
    }
}
