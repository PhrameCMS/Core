<?php

declare(strict_types=1);

namespace PhrameCMS\Core\Contracts;

interface RouteProviderInterface
{
    /**
     * @return array<int, \PhrameCMS\Core\Routing\Route>
     */
    public function routes(): array;
}
