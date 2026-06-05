<?php

declare(strict_types=1);

namespace PhrameCMS\Core\Routing;

use Closure;
use PhrameCMS\Core\Http\HttpMethod;

final class Route
{
    /**
     * @param Closure(\PhrameCMS\Core\Http\Request, \PhrameCMS\Core\Contracts\ContainerBuilderInterface): \PhrameCMS\Core\Http\Response $handler
     */
    public function __construct(
        public readonly HttpMethod $method,
        public readonly string $path,
        public readonly Closure $handler,
    ) {
    }

    /**
     * @param callable(\PhrameCMS\Core\Http\Request, \PhrameCMS\Core\Contracts\ContainerBuilderInterface): \PhrameCMS\Core\Http\Response $handler
     */
    public static function create(HttpMethod $method, string $path, callable $handler): self
    {
        return new self($method, $path, Closure::fromCallable($handler));
    }

    public function matches(HttpMethod $method, string $path): bool
    {
        return $method === $this->method && $path === $this->path;
    }
}
