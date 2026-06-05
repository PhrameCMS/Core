<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PhrameCMS\Core\Contracts\ServiceTag;
use PhrameCMS\Core\SymfonyContainer;

final class SymfonyContainerTest extends TestCase
{
    protected function setUp(): void
    {
        if (!SymfonyContainer::isAvailable()) {
            self::markTestSkipped('Symfony DI is unavailable in this environment.');
        }
    }

    public function testSharedServiceIsMemoized(): void
    {
        $container = new SymfonyContainer();
        $calls = 0;

        $container->set('counter', static function () use (&$calls): int {
            $calls++;

            return $calls;
        });

        self::assertSame(1, $container->get('counter'));
        self::assertSame(1, $container->get('counter'));
        self::assertSame(1, $calls);
    }

    public function testSharedScalarServiceIsMemoized(): void
    {
        $container = new SymfonyContainer();
        $calls = 0;

        $container->set('scalar.counter', static function () use (&$calls): int {
            $calls++;

            return $calls;
        });

        self::assertSame(1, $container->get('scalar.counter'));
        self::assertSame(1, $container->get('scalar.counter'));
        self::assertSame(1, $calls);
    }

    public function testNonSharedServiceResolvesEachTime(): void
    {
        $container = new SymfonyContainer();
        $calls = 0;

        $container->set('counter', static function () use (&$calls): int {
            $calls++;

            return $calls;
        }, false);

        self::assertSame(1, $container->get('counter'));
        self::assertSame(2, $container->get('counter'));
        self::assertSame(2, $calls);
    }

    public function testMissingServiceThrows(): void
    {
        $container = new SymfonyContainer();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Service "missing" is not defined.');

        $container->get('missing');
    }

    public function testTagsSupportEnumAndStringAndDeduplicate(): void
    {
        $container = new SymfonyContainer();

        $container->tag(ServiceTag::RouteProvider, 'route.provider.class');
        $container->tag('route.provider', 'route.provider.class');
        $container->tag(' route.provider ', 'another.provider.class');

        self::assertSame(['route.provider.class', 'another.provider.class'], $container->tagged(ServiceTag::RouteProvider));
    }
}
