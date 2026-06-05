<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PhrameCMS\Core\Contracts\ContainerBuilderInterface;
use PhrameCMS\Core\Contracts\RoutingEngineInterface;
use PhrameCMS\Core\Http\Request;
use PhrameCMS\Core\Http\Response;
use PhrameCMS\Core\Routing\NativeRoutingEngine;
use PhrameCMS\Core\Routing\RoutingEngineFactory;

final class RoutingEngineFactoryTest extends TestCase
{
    private string|false $original;

    protected function setUp(): void
    {
        $this->original = getenv('PHRAME_ROUTING_ENGINE');
    }

    protected function tearDown(): void
    {
        if ($this->original === false) {
            putenv('PHRAME_ROUTING_ENGINE');
        } else {
            putenv('PHRAME_ROUTING_ENGINE=' . $this->original);
        }
    }

    public function testConfiguredNativeReturnsNativeRoutingEngine(): void
    {
        putenv('PHRAME_ROUTING_ENGINE=native');

        self::assertInstanceOf(NativeRoutingEngine::class, RoutingEngineFactory::createDefault());
    }

    public function testConfiguredBridgeUsesPreferredEngineWhenAvailableOrFallsBack(): void
    {
        putenv('PHRAME_ROUTING_ENGINE=routing-bridge');

        $engine = RoutingEngineFactory::createDefault();

        if (self::isRoutingBridgeInstalled()) {
            self::assertContains($engine::class, [
                'PhrameCMS\\RoutingBridge\\RoutingBridge',
            ]);

            return;
        }

        self::assertInstanceOf(NativeRoutingEngine::class, $engine);
    }

    public function testInvalidClassThrows(): void
    {
        putenv('PHRAME_ROUTING_ENGINE=Nope\\MissingEngine');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('class was not found');

        RoutingEngineFactory::createDefault();
    }

    public function testCustomEngineClassIsSupported(): void
    {
        putenv('PHRAME_ROUTING_ENGINE=CustomRoutingEngineForFactoryTest');

        self::assertInstanceOf(CustomRoutingEngineForFactoryTest::class, RoutingEngineFactory::createDefault());
    }

    private static function isRoutingBridgeInstalled(): bool
    {
        $bridgeClass = 'PhrameCMS\\RoutingBridge\\RoutingBridge';

        return class_exists($bridgeClass) && $bridgeClass::isAvailable();
    }
}

final class CustomRoutingEngineForFactoryTest implements RoutingEngineInterface
{
    public function dispatch(Request $request, array $routes, ContainerBuilderInterface $container): ?Response
    {
        if ($routes === []) {
            return null;
        }

        return Response::json(['ok' => true]);
    }
}
