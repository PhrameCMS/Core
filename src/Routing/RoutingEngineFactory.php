<?php

declare(strict_types=1);

namespace PhrameCMS\Core\Routing;

use PhrameCMS\Core\Contracts\RoutingEngineInterface;
use RuntimeException;
use Throwable;

final class RoutingEngineFactory
{
    /**
     * @var list<string>
     */
    private const PREFERRED_ENGINE_CANDIDATES = [
        'PhrameCMS\\RoutingBridge\\RoutingBridge',
    ];

    public static function createDefault(): RoutingEngineInterface
    {
        $configured = trim((string) (getenv('PHRAME_ROUTING_ENGINE') ?: ''));

        if ($configured !== '') {
            return self::fromConfiguration($configured);
        }

        $engine = self::createPreferredEngine();
        if ($engine !== null) {
            return $engine;
        }

        return new NativeRoutingEngine();
    }

    private static function fromConfiguration(string $configured): RoutingEngineInterface
    {
        $mode = RoutingEngineMode::fromConfiguration($configured);

        if ($mode === RoutingEngineMode::Native) {
            return new NativeRoutingEngine();
        }

        if ($mode === RoutingEngineMode::Bridge) {
            $engine = self::createPreferredEngine();
            if ($engine !== null) {
                return $engine;
            }

            return new NativeRoutingEngine();
        }

        if (!class_exists($configured)) {
            throw new RuntimeException(sprintf(
                'Invalid PHRAME_ROUTING_ENGINE "%s": class was not found.',
                $configured,
            ));
        }

        try {
            $engine = new $configured();
        } catch (Throwable $exception) {
            throw new RuntimeException(sprintf(
                'Invalid PHRAME_ROUTING_ENGINE "%s": class could not be instantiated (%s).',
                $configured,
                $exception->getMessage(),
            ), (int) $exception->getCode(), $exception);
        }

        if (!$engine instanceof RoutingEngineInterface) {
            throw new RuntimeException(sprintf(
                'Invalid PHRAME_ROUTING_ENGINE "%s": class must implement %s.',
                $configured,
                RoutingEngineInterface::class,
            ));
        }

        return $engine;
    }

    private static function createPreferredEngine(): ?RoutingEngineInterface
    {
        foreach (self::PREFERRED_ENGINE_CANDIDATES as $engineClass) {
            if (!class_exists($engineClass)) {
                continue;
            }

            if (!$engineClass::isAvailable()) {
                continue;
            }

            return new $engineClass();
        }

        return null;
    }
}