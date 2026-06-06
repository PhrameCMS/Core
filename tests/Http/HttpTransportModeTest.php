<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PhrameCMS\Core\Http\HttpTransportMode;

final class HttpTransportModeTest extends TestCase
{
    public function testFromConfigurationSupportsAliases(): void
    {
        self::assertSame(HttpTransportMode::Native, HttpTransportMode::fromConfiguration(' native '));
        self::assertSame(HttpTransportMode::Bridge, HttpTransportMode::fromConfiguration('bridge'));
        self::assertSame(HttpTransportMode::Bridge, HttpTransportMode::fromConfiguration('http-foundation-bridge'));
        self::assertSame(HttpTransportMode::Bridge, HttpTransportMode::fromConfiguration('symfony'));
        self::assertSame(HttpTransportMode::Bridge, HttpTransportMode::fromConfiguration('httpfoundation'));
        self::assertSame(HttpTransportMode::Bridge, HttpTransportMode::fromConfiguration('http-foundation'));
        self::assertNull(HttpTransportMode::fromConfiguration('unknown'));
    }
}
