<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PhrameCMS\Core\Plugin\PluginDefinition;

final class PluginDefinitionTest extends TestCase
{
    public function testStoresReadonlyProperties(): void
    {
        $definition = new PluginDefinition('vendor/package', ['ProviderA'], ['feature.a']);

        self::assertSame('vendor/package', $definition->package);
        self::assertSame(['ProviderA'], $definition->providers);
        self::assertSame(['feature.a'], $definition->capabilities);
    }
}
