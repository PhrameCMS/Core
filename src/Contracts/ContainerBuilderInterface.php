<?php

declare(strict_types=1);

namespace Lume\Core\Contracts;

interface ContainerBuilderInterface
{
    public function get(string $id): mixed;

    public function has(string $id): bool;

    public function set(string $id, mixed $concrete, bool $shared = true): void;

    public function tag(string $tag, string $serviceId): void;

    /**
     * @return array<int, string>
     */
    public function tagged(string $tag): array;
}
