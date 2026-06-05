<?php

declare(strict_types=1);

namespace Lume\Core\Routing;

use Closure;

final class Route
{
    /**
     * @param Closure(\Lume\Core\Http\Request, \Lume\Core\Contracts\ContainerBuilderInterface): \Lume\Core\Http\Response $handler
     */
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly Closure $handler,
    ) {
    }

    /**
     * @param callable(\Lume\Core\Http\Request, \Lume\Core\Contracts\ContainerBuilderInterface): \Lume\Core\Http\Response $handler
     */
    public static function create(string $method, string $path, callable $handler): self
    {
        return new self(strtoupper($method), $path, Closure::fromCallable($handler));
    }

    public function matches(string $method, string $path): bool
    {
        return strtoupper($method) === $this->method && $path === $this->path;
    }
}
