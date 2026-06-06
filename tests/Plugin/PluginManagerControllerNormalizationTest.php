<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PhrameCMS\Core\Http\HttpMethod;
use PhrameCMS\Core\Plugin\PluginManager;

final class PluginManagerControllerNormalizationTest extends TestCase
{
    public function testNormalizeControllerListBuildsRouteDefinitions(): void
    {
        $manager = new PluginManager();

        $method = new ReflectionMethod($manager, 'normalizeControllerList');
        $method->setAccessible(true);

        $result = $method->invoke($manager, [
            [
                'method' => 'GET',
                'path' => '/page',
                'controller' => 'Vendor\\PageController',
                'name' => 'page.show',
                'defaults' => ['foo' => 'bar'],
                'requirements' => ['id' => '\\d+'],
            ],
            [
                'method' => 'INVALID',
                'path' => '/skip',
                'controller' => 'Vendor\\SkipController',
            ],
        ]);

        self::assertCount(1, $result);
        self::assertSame(HttpMethod::GET, $result[0]->method);
        self::assertSame('/page', $result[0]->path);
        self::assertSame('Vendor\\PageController', $result[0]->controller);
        self::assertSame('page.show', $result[0]->name);
        self::assertSame(['foo' => 'bar'], $result[0]->defaults);
        self::assertSame(['id' => '\\d+'], $result[0]->requirements);
    }
}
