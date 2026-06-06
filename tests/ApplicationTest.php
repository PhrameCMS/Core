<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PhrameCMS\Core\Application;
use PhrameCMS\Core\Contracts\DatabaseAdapterInterface;
use PhrameCMS\Core\CoreContainer;
use PhrameCMS\Core\Http\HttpMethod;
use PhrameCMS\Core\Http\Request;
use PhrameCMS\Core\Plugin\PluginManager;

final class ApplicationTest extends TestCase
{
    public function testHandleReturnsHealthResponseAfterBootstrap(): void
    {
        $app = new Application(new CoreContainer(), new PluginManager());
        $app->bootstrap();

        $request = new Request(HttpMethod::GET, '/health', [], [], null);
        $response = $app->handle($request);

        self::assertSame(200, $response->status());
        self::assertStringContainsString('"status": "ok"', $response->body());
    }

    public function testHandleReturnsNotFoundForUnknownPath(): void
    {
        $app = new Application(new CoreContainer(), new PluginManager());
        $app->bootstrap();

        $request = new Request(HttpMethod::GET, '/does-not-exist', [], [], null);
        $response = $app->handle($request);

        self::assertSame(404, $response->status());
        self::assertStringContainsString('Not Found', $response->body());
    }

    public function testBootstrapRegistersDatabaseAdapterOnlyWhenAvailable(): void
    {
        $app = new Application(new CoreContainer(), new PluginManager());
        $app->bootstrap();

        $containerProperty = new ReflectionProperty(Application::class, 'container');
        $containerProperty->setAccessible(true);

        $container = $containerProperty->getValue($app);

        self::assertInstanceOf(CoreContainer::class, $container);

        $bridgeClass = 'PhrameCMS\\DoctrineDbalBridge\\DoctrineDbalBridge';
        if (class_exists($bridgeClass) && $bridgeClass::isAvailable()) {
            self::assertTrue($container->has(DatabaseAdapterInterface::class));

            return;
        }

        self::assertFalse($container->has(DatabaseAdapterInterface::class));
    }
}
