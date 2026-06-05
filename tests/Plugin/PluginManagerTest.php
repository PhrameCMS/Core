<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PhrameCMS\Core\Plugin\PluginDefinition;
use PhrameCMS\Core\Plugin\PluginManager;

final class PluginManagerTest extends TestCase
{
    public function testDiscoverReturnsPluginDefinitions(): void
    {
        $manager = new PluginManager();
        $definitions = $manager->discover();

        self::assertIsArray($definitions);

        foreach ($definitions as $definition) {
            self::assertInstanceOf(PluginDefinition::class, $definition);
            self::assertNotSame('', trim($definition->package));
            self::assertNotSame([], $definition->providers);
        }
    }
}
