<?php

declare(strict_types=1);

namespace Maslosoft\Signals\Helpers;

use Maslosoft\Cli\Shared\ConfigDetector;
use Maslosoft\Signals\Builder\IO\PhpFile;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;
use UnexpectedValueException;

/** @internal Read configuration without mutating a worker's shared filesystem. */
final class Configuration
{
    public static function read(string $name, string $runtimePath = 'runtime'): array
    {
        if (PHP_SAPI === 'cli') {
            $source = self::absolutePath($name . '.yml');
            if (file_exists($source)) {
                $contents = @file_get_contents($source);
                if ($contents === false) {
                    throw new RuntimeException(sprintf('Cannot read Signals configuration "%s".', $source));
                }
                $configuration = Yaml::parse($contents);
                if ($configuration !== null && !is_array($configuration)) {
                    throw new UnexpectedValueException(sprintf('Signals configuration "%s" must contain an array.', $source));
                }
                if ($configuration !== null && $configuration !== []) {
                    return $configuration;
                }
            }
        }
        return self::cache($name, $runtimePath)->read();
    }

    /** Publish the configuration cache explicitly as part of a successful build. */
    public static function write(string $name, string $runtimePath, array $configuration): void
    {
        self::cache($name, $runtimePath)->write($configuration);
    }

    private static function cache(string $name, string $runtimePath): PhpFile
    {
        $io = new PhpFile();
        $io->generatedPath = self::absolutePath($runtimePath);
        $io->configFilename = $name . '.php';
        return $io;
    }

    private static function absolutePath(string $path): string
    {
        if (str_starts_with($path, '/') || str_starts_with($path, '\\') || preg_match('~^[A-Za-z]:[/\\\\]~', $path)) {
            return $path;
        }
        return rtrim((new ConfigDetector())->getRootPath(), '/\\') . '/' . $path;
    }
}
