<?php

declare(strict_types=1);

namespace PhrameCMS\Core;

use PhrameCMS\Core\Contracts\ContainerBuilderInterface;
use PhrameCMS\Core\Contracts\ServiceTag;
use Throwable;

final class CoreContainer implements ContainerBuilderInterface
{
    /**
     * @var array<string, array{concrete:mixed, shared:bool}>
     */
    private array $definitions = [];

    /**
     * @var array<string, mixed>
     */
    private array $sharedInstances = [];

    /**
     * @var array<string, array<int, string>>
     */
    private array $tags = [];

    public function set(string $id, mixed $concrete, bool $shared = true): void
    {
        $this->definitions[$id] = [
            'concrete' => $concrete,
            'shared' => $shared,
        ];

        if (isset($this->sharedInstances[$id])) {
            unset($this->sharedInstances[$id]);
        }
    }

    public function get(string $id): mixed
    {
        if (!isset($this->definitions[$id])) {
            throw new \RuntimeException(sprintf('Service "%s" is not defined.', $id));
        }

        $definition = $this->definitions[$id];

        if ($definition['shared'] && array_key_exists($id, $this->sharedInstances)) {
            return $this->sharedInstances[$id];
        }

        try {
            $resolved = $this->resolve($definition['concrete']);
        } catch (Throwable $exception) {
            throw new \RuntimeException($exception->getMessage(), (int) $exception->getCode(), $exception);
        }

        if ($definition['shared']) {
            $this->sharedInstances[$id] = $resolved;
        }

        return $resolved;
    }

    public function has(string $id): bool
    {
        return isset($this->definitions[$id]);
    }

    public function tag(ServiceTag|string $tag, string $serviceId): void
    {
        $tagKey = $this->normalizeTag($tag);

        if (!isset($this->tags[$tagKey])) {
            $this->tags[$tagKey] = [];
        }

        if (!in_array($serviceId, $this->tags[$tagKey], true)) {
            $this->tags[$tagKey][] = $serviceId;
        }
    }

    public function tagged(ServiceTag|string $tag): array
    {
        return $this->tags[$this->normalizeTag($tag)] ?? [];
    }

    private function resolve(mixed $concrete): mixed
    {
        if (is_callable($concrete)) {
            return $concrete($this);
        }

        return $concrete;
    }

    private function normalizeTag(ServiceTag|string $tag): string
    {
        if ($tag instanceof ServiceTag) {
            return $tag->value;
        }

        return trim($tag);
    }
}
