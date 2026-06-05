<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PhrameCMS\Core\Routing\RoutingEngineMode;

final class RoutingEngineModeTest extends TestCase
{
    public function testFromConfigurationSupportsAliases(): void
    {
        self::assertSame(RoutingEngineMode::Native, RoutingEngineMode::fromConfiguration(' native '));
        self::assertSame(RoutingEngineMode::Bridge, RoutingEngineMode::fromConfiguration('routing-bridge'));
        self::assertSame(RoutingEngineMode::Bridge, RoutingEngineMode::fromConfiguration('symfony-routing'));
        self::assertSame(RoutingEngineMode::Bridge, RoutingEngineMode::fromConfiguration('symfony'));
        self::assertSame(RoutingEngineMode::Bridge, RoutingEngineMode::fromConfiguration('routing'));
        self::assertNull(RoutingEngineMode::fromConfiguration('unknown'));
    }
}
