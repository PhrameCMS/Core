<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PhrameCMS\Core\Database\DatabaseMode;

final class DatabaseModeTest extends TestCase
{
    public function testFromConfigurationSupportsAliases(): void
    {
        self::assertSame(DatabaseMode::None, DatabaseMode::fromConfiguration(' none '));
        self::assertSame(DatabaseMode::None, DatabaseMode::fromConfiguration('native'));
        self::assertSame(DatabaseMode::None, DatabaseMode::fromConfiguration('off'));
        self::assertSame(DatabaseMode::Bridge, DatabaseMode::fromConfiguration('bridge'));
        self::assertSame(DatabaseMode::Bridge, DatabaseMode::fromConfiguration('doctrine'));
        self::assertSame(DatabaseMode::Bridge, DatabaseMode::fromConfiguration('dbal'));
        self::assertSame(DatabaseMode::Bridge, DatabaseMode::fromConfiguration('doctrine-dbal-bridge'));
        self::assertNull(DatabaseMode::fromConfiguration('unknown'));
    }
}
