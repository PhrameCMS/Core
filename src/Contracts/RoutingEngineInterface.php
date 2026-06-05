<?php

declare(strict_types=1);

namespace PhrameCMS\Core\Contracts;

use PhrameCMS\Core\Http\Request;
use PhrameCMS\Core\Http\Response;

interface RoutingEngineInterface
{
    /**
     * @param array<int, \PhrameCMS\Core\Routing\Route> $routes
     */
    public function dispatch(Request $request, array $routes, ContainerBuilderInterface $container): ?Response;
}