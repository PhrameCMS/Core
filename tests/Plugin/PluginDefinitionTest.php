<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PhrameCMS\Core\Http\HttpMethod;
use PhrameCMS\Core\Plugin\ControllerRouteDefinition;
use PhrameCMS\Core\Plugin\PluginDefinition;

final class PluginDefinitionTest extends TestCase
{
    public function testStoresReadonlyProperties(): void
    {
        $definition = new PluginDefinition(
            'vendor/package',
            ['ProviderA'],
            ['feature.a'],
            [new ControllerRouteDefinition(HttpMethod::GET, '/page', 'Vendor\\PageController')],
        );

        self::assertSame('vendor/package', $definition->package);
        self::assertSame(['ProviderA'], $definition->providers);
        self::assertSame(['feature.a'], $definition->capabilities);
        self::assertCount(1, $definition->controllers);
    }
}
