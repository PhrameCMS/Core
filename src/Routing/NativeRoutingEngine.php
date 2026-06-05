<?php

declare(strict_types=1);

namespace PhrameCMS\Core\Routing;

use PhrameCMS\Core\Contracts\ContainerBuilderInterface;
use PhrameCMS\Core\Contracts\RoutingEngineInterface;
use PhrameCMS\Core\Http\Request;
use PhrameCMS\Core\Http\Response;

final class NativeRoutingEngine implements RoutingEngineInterface
{
    /**
     * @param array<int, Route> $routes
     */
    public function dispatch(Request $request, array $routes, ContainerBuilderInterface $container): ?Response
    {
        foreach ($routes as $route) {
            if (!$route->matches($request->method, $request->path)) {
                continue;
            }

            return ($route->handler)($request, $container);
        }

        return null;
    }
}