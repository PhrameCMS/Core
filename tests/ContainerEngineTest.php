<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PhrameCMS\Core\ContainerEngine;

final class ContainerEngineTest extends TestCase
{
    public function testFromConfigurationSupportsAliases(): void
    {
        self::assertSame(ContainerEngine::Native, ContainerEngine::fromConfiguration(' native '));
        self::assertSame(ContainerEngine::Symfony, ContainerEngine::fromConfiguration('symfony'));
        self::assertSame(ContainerEngine::Symfony, ContainerEngine::fromConfiguration('symfony-di'));
        self::assertSame(ContainerEngine::Symfony, ContainerEngine::fromConfiguration('dependency-injection'));
        self::assertNull(ContainerEngine::fromConfiguration('unknown'));
    }
}
