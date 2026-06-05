<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PhrameCMS\Core\Http\HttpMethod;
use PhrameCMS\Core\Http\Request;
use PhrameCMS\Core\Http\Response;
use PhrameCMS\Core\Routing\Route;

final class RouteTest extends TestCase
{
    public function testCreateAndMatch(): void
    {
        $route = Route::create(HttpMethod::GET, '/ping', static fn (Request $request, mixed $container): Response => Response::json([
            'path' => $request->path,
        ]));

        self::assertTrue($route->matches(HttpMethod::GET, '/ping'));
        self::assertFalse($route->matches(HttpMethod::POST, '/ping'));
        self::assertFalse($route->matches(HttpMethod::GET, '/pong'));
    }
}
