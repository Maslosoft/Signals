<?php

declare(strict_types=1);

namespace Helpers;

use Codeception\Test\Unit;
use Maslosoft\Signals\Builder\IO\Memory;
use Maslosoft\Signals\Helpers\Preview;
use Maslosoft\Signals\Helpers\Renderer;
use Maslosoft\Signals\Interfaces\ExtractorInterface;
use Maslosoft\Signals\Signal;

final class PreviewTest extends Unit
{
    public function testRendererShowsInjectionNamesAndOmitsDisabledEntries(): void
    {
        $lines = (new Renderer())->renderCli(['signals' => [
            'Event' => ['Listener' => ['onEvent', 'event'], 'Disabled' => false],
        ]]);
        self::assertContains("\tListener::onEvent", $lines);
        self::assertContains("\tListener::event", $lines);
        self::assertStringNotContainsString('Disabled', implode("\n", $lines));
    }

    public function testPreviewRestoresTheConfiguredIOAfterSuccessAndFailure(): void
    {
        $signal = new Signal('preview-test');
        $io = new Memory();
        $io->write(['signals' => ['Original' => []]]);
        $signal->setIO($io);
        $extractor = new class implements ExtractorInterface {
            public bool $fail = false;
            public function setSignal(Signal $signal): void {}
            public function getData(): array
            {
                if ($this->fail) {
                    throw new \RuntimeException('Failed to scan.');
                }
                return ['signals' => [], 'slots' => []];
            }
        };
        $signal->setExtractor($extractor);
        $preview = new Preview();
        self::assertContains('Signals:', $preview->cli($signal));
        self::assertSame($io, $signal->getIO());
        self::assertSame(['signals' => ['Original' => []]], $io->read());
        $extractor->fail = true;
        try {
            $preview->cli($signal);
            self::fail('Expected the scan failure.');
        } catch (\RuntimeException $exception) {
            self::assertSame('Failed to scan.', $exception->getMessage());
        }
        self::assertSame($io, $signal->getIO());
    }
}
