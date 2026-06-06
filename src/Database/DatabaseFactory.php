<?php

declare(strict_types=1);

namespace PhrameCMS\Core\Database;

use PhrameCMS\Core\Contracts\DatabaseAdapterInterface;
use RuntimeException;
use Throwable;

final class DatabaseFactory
{
    /**
     * @var list<string>
     */
    private const PREFERRED_ADAPTER_CANDIDATES = [
        'PhrameCMS\\DoctrineDbalBridge\\DoctrineDbalBridge',
    ];

    public static function createDefault(): ?DatabaseAdapterInterface
    {
        $configured = trim((string) (getenv('PHRAME_DATABASE') ?: ''));

        if ($configured !== '') {
            return self::fromConfiguration($configured);
        }

        return self::createPreferredAdapter();
    }

    private static function fromConfiguration(string $configured): ?DatabaseAdapterInterface
    {
        $mode = DatabaseMode::fromConfiguration($configured);

        if ($mode === DatabaseMode::None) {
            return null;
        }

        if ($mode === DatabaseMode::Bridge) {
            return self::createPreferredAdapter();
        }

        if (!class_exists($configured)) {
            throw new RuntimeException(sprintf(
                'Invalid PHRAME_DATABASE "%s": class was not found.',
                $configured,
            ));
        }

        try {
            $adapter = new $configured();
        } catch (Throwable $exception) {
            throw new RuntimeException(sprintf(
                'Invalid PHRAME_DATABASE "%s": class could not be instantiated (%s).',
                $configured,
                $exception->getMessage(),
            ), (int) $exception->getCode(), $exception);
        }

        if (!$adapter instanceof DatabaseAdapterInterface) {
            throw new RuntimeException(sprintf(
                'Invalid PHRAME_DATABASE "%s": class must implement %s.',
                $configured,
                DatabaseAdapterInterface::class,
            ));
        }

        return $adapter;
    }

    private static function createPreferredAdapter(): ?DatabaseAdapterInterface
    {
        foreach (self::PREFERRED_ADAPTER_CANDIDATES as $adapterClass) {
            if (!class_exists($adapterClass)) {
                continue;
            }

            if (!$adapterClass::isAvailable()) {
                continue;
            }

            try {
                $adapter = new $adapterClass();
            } catch (Throwable) {
                continue;
            }

            if (!$adapter instanceof DatabaseAdapterInterface) {
                continue;
            }

            return $adapter;
        }

        return null;
    }
}
