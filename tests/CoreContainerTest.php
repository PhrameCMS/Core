<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PhrameCMS\Core\CoreContainer;
use PhrameCMS\Core\Contracts\ServiceTag;

final class CoreContainerTest extends TestCase
{
    public function testSharedServiceIsMemoized(): void
    {
        $container = new CoreContainer();
        $calls = 0;

        $container->set('counter', static function () use (&$calls): int {
            $calls++;

            return $calls;
        });

        self::assertSame(1, $container->get('counter'));
        self::assertSame(1, $container->get('counter'));
        self::assertSame(1, $calls);
    }

    public function testNonSharedServiceResolvesEachTime(): void
    {
        $container = new CoreContainer();
        $calls = 0;

        $container->set('counter', static function () use (&$calls): int {
            $calls++;

            return $calls;
        }, false);

        self::assertSame(1, $container->get('counter'));
        self::assertSame(2, $container->get('counter'));
        self::assertSame(2, $calls);
    }

    public function testGetThrowsForMissingService(): void
    {
        $container = new CoreContainer();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Service "missing" is not defined.');

        $container->get('missing');
    }

    public function testTagsSupportEnumAndStringAndDeduplicate(): void
    {
        $container = new CoreContainer();

        $container->tag(ServiceTag::RouteProvider, 'a');
        $container->tag('route.provider', 'a');
        $container->tag(' route.provider ', 'b');

        self::assertSame(['a', 'b'], $container->tagged(ServiceTag::RouteProvider));
        self::assertSame(['a', 'b'], $container->tagged('route.provider'));
    }
}
