<?php

declare(strict_types=1);

namespace PhrameCMS\Core\Controller;

use PhrameCMS\Core\Contracts\ContainerBuilderInterface;
use PhrameCMS\Core\Contracts\ControllerResolverInterface;
use RuntimeException;
use Throwable;

final class ControllerResolverFactory
{
    /**
     * @var list<string>
     */
    private const PREFERRED_RESOLVER_CANDIDATES = [
        'PhrameCMS\\ControllerBridge\\ControllerBridge',
    ];

    public static function createDefault(): ControllerResolverInterface
    {
        $configured = trim((string) (getenv('PHRAME_CONTROLLER_RESOLVER') ?: ''));

        if ($configured !== '') {
            return self::fromConfiguration($configured);
        }

        $resolver = self::createPreferredResolver();
        if ($resolver !== null) {
            return $resolver;
        }

        return new NativeControllerResolver();
    }

    private static function fromConfiguration(string $configured): ControllerResolverInterface
    {
        $mode = ControllerResolverMode::fromConfiguration($configured);

        if ($mode === ControllerResolverMode::Native) {
            return new NativeControllerResolver();
        }

        if ($mode === ControllerResolverMode::Bridge) {
            $resolver = self::createPreferredResolver();
            if ($resolver !== null) {
                return $resolver;
            }

            return new NativeControllerResolver();
        }

        if (!class_exists($configured)) {
            throw new RuntimeException(sprintf(
                'Invalid PHRAME_CONTROLLER_RESOLVER "%s": class was not found.',
                $configured,
            ));
        }

        try {
            $resolver = new $configured();
        } catch (Throwable $exception) {
            throw new RuntimeException(sprintf(
                'Invalid PHRAME_CONTROLLER_RESOLVER "%s": class could not be instantiated (%s).',
                $configured,
                $exception->getMessage(),
            ), (int) $exception->getCode(), $exception);
        }

        if (!$resolver instanceof ControllerResolverInterface) {
            throw new RuntimeException(sprintf(
                'Invalid PHRAME_CONTROLLER_RESOLVER "%s": class must implement %s.',
                $configured,
                ControllerResolverInterface::class,
            ));
        }

        return $resolver;
    }

    private static function createPreferredResolver(): ?ControllerResolverInterface
    {
        foreach (self::PREFERRED_RESOLVER_CANDIDATES as $resolverClass) {
            if (!class_exists($resolverClass)) {
                continue;
            }

            if (!$resolverClass::isAvailable()) {
                continue;
            }

            return new $resolverClass();
        }

        return null;
    }
}