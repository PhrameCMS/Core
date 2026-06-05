<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PhrameCMS\Core\Http\HttpTransportMode;

final class HttpTransportModeTest extends TestCase
{
    public function testFromConfigurationSupportsAliases(): void
    {
        self::assertSame(HttpTransportMode::Native, HttpTransportMode::fromConfiguration(' native '));
        self::assertSame(HttpTransportMode::Symfony, HttpTransportMode::fromConfiguration('symfony'));
        self::assertSame(HttpTransportMode::Symfony, HttpTransportMode::fromConfiguration('httpfoundation'));
        self::assertSame(HttpTransportMode::Symfony, HttpTransportMode::fromConfiguration('http-foundation'));
        self::assertNull(HttpTransportMode::fromConfiguration('unknown'));
    }
}
