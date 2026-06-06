<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PhrameCMS\Core\Contracts\TemplateRendererInterface;
use PhrameCMS\Core\Template\TemplateRendererFactory;

final class TemplateRendererFactoryTest extends TestCase
{
    private string|false $original;

    protected function setUp(): void
    {
        $this->original = getenv('PHRAME_TEMPLATE_RENDERER');
    }

    protected function tearDown(): void
    {
        if ($this->original === false) {
            putenv('PHRAME_TEMPLATE_RENDERER');
        } else {
            putenv('PHRAME_TEMPLATE_RENDERER=' . $this->original);
        }
    }

    public function testConfiguredNoneReturnsNull(): void
    {
        putenv('PHRAME_TEMPLATE_RENDERER=none');

        self::assertNull(TemplateRendererFactory::createDefault());
    }

    public function testInvalidConfiguredClassThrows(): void
    {
        putenv('PHRAME_TEMPLATE_RENDERER=Nope\\MissingRenderer');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('class was not found');

        TemplateRendererFactory::createDefault();
    }

    public function testCustomRendererClassIsSupported(): void
    {
        putenv('PHRAME_TEMPLATE_RENDERER=CustomTemplateRendererForFactoryTest');

        self::assertInstanceOf(CustomTemplateRendererForFactoryTest::class, TemplateRendererFactory::createDefault());
    }
}

final class CustomTemplateRendererForFactoryTest implements TemplateRendererInterface
{
    public function render(string $template, array $context = []): string
    {
        return $template;
    }
}
