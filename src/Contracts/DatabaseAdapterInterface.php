<?php

declare(strict_types=1);

namespace PhrameCMS\Core\Contracts;

interface DatabaseAdapterInterface
{
    /**
        * @param array<int|string, mixed> $params
     */
    public function execute(string $sql, array $params = []): int;

    /**
        * @param array<int|string, mixed> $params
     * @return array<int, array<string, mixed>>
     */
    public function fetchAll(string $sql, array $params = []): array;

    /**
        * @param array<int|string, mixed> $params
     * @return array<string, mixed>|null
     */
    public function fetchOne(string $sql, array $params = []): ?array;

    public function beginTransaction(): void;

    public function commit(): void;

    public function rollback(): void;
}