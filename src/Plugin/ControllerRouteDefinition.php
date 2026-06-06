<?php

declare(strict_types=1);

namespace PhrameCMS\Core\Plugin;

use PhrameCMS\Core\Http\HttpMethod;

final class ControllerRouteDefinition
{
    /**
     * @param array<string, mixed> $defaults
     * @param array<string, mixed> $requirements
     */
    public function __construct(
        public readonly HttpMethod $method,
        public readonly string $path,
        public readonly string $controller,
        public readonly ?string $name = null,
        public readonly array $defaults = [],
        public readonly array $requirements = [],
    ) {
    }
}