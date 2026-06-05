<?php

declare(strict_types=1);

namespace PhrameCMS\Core;

use PhrameCMS\Core\Contracts\ContainerBuilderInterface;
use PhrameCMS\Core\Contracts\ServiceTag;
use RuntimeException;
use Throwable;

final class SymfonyContainer implements ContainerBuilderInterface
{
    private const CONTAINER_BUILDER_CLASS = 'Symfony\\Component\\DependencyInjection\\ContainerBuilder';

    /**
     * @var object
     */
    private object $container;

    /**
     * @var array<string, array{concrete:mixed, shared:bool}>
     */
    private array $definitions = [];

    /**
     * @var array<string, true>
     */
    private array $initializedSharedIds = [];

    /**
     * @var array<string, array<int, string>>
     */
    private array $tags = [];

    public function __construct()
    {
        if (!self::isAvailable()) {
            throw new RuntimeException('Symfony DependencyInjection component is not available.');
        }

        $containerClass = self::CONTAINER_BUILDER_CLASS;
        $this->container = new $containerClass();
    }

    public static function isAvailable(): bool
    {
        return class_exists(self::CONTAINER_BUILDER_CLASS);
    }

    public function set(string $id, mixed $concrete, bool $shared = true): void
    {
        $this->definitions[$id] = [
            'concrete' => $concrete,
            'shared' => $shared,
        ];

        if (isset($this->initializedSharedIds[$id])) {
            $this->container->set($id, null);
            unset($this->initializedSharedIds[$id]);
        }
    }

    public function get(string $id): mixed
    {
        if (!isset($this->definitions[$id])) {
            throw new RuntimeException(sprintf('Service "%s" is not defined.', $id));
        }

        $definition = $this->definitions[$id];

        if ($definition['shared'] && isset($this->initializedSharedIds[$id])) {
            return $this->container->get($id);
        }

        try {
            $resolved = $this->resolve($definition['concrete']);
        } catch (Throwable $exception) {
            throw new RuntimeException($exception->getMessage(), (int) $exception->getCode(), $exception);
        }

        if ($definition['shared']) {
            $this->container->set($id, $resolved);
            $this->initializedSharedIds[$id] = true;
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
