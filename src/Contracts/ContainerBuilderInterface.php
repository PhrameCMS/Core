<?php

declare(strict_types=1);

namespace PhrameCMS\Core\Contracts;

interface ContainerBuilderInterface
{
    public function get(string $id): mixed;

    public function has(string $id): bool;

    public function set(string $id, mixed $concrete, bool $shared = true): void;

    public function tag(ServiceTag|string $tag, string $serviceId): void;

    /**
     * @return array<int, string>
     */
    public function tagged(ServiceTag|string $tag): array;
}
