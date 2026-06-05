<?php

declare(strict_types=1);

namespace Lume\Core\Contracts;

interface RouteProviderInterface
{
    /**
     * @return array<int, \Lume\Core\Routing\Route>
     */
    public function routes(): array;
}
