<?php

declare(strict_types=1);

namespace PhrameCMS\Core;

use PhrameCMS\Core\Contracts\ContainerBuilderInterface;
use RuntimeException;
use Throwable;

final class ContainerFactory
{
    /**
     * @var list<class-string>
     */
    private const PREFERRED_CONTAINER_CANDIDATES = [
        'PhrameCMS\\DependencyInjectionBridge\\DependencyInjectionBridge',
    ];

    public static function createDefault(): ContainerBuilderInterface
    {
        $configured = trim((string) (getenv('PHRAME_CONTAINER') ?: ''));

        if ($configured !== '') {
            return self::fromConfiguration($configured);
        }

        $container = self::createPreferredContainer();
        if ($container !== null) {
            return $container;
        }

        return new CoreContainer();
    }

    private static function fromConfiguration(string $configured): ContainerBuilderInterface
    {
        $engine = ContainerEngine::fromConfiguration($configured);

        if ($engine === ContainerEngine::Native) {
            return new CoreContainer();
        }

        if ($engine === ContainerEngine::DependencyInjection) {
            $container = self::createPreferredContainer();
            if ($container !== null) {
                return $container;
            }

            throw new RuntimeException(
                sprintf('Invalid PHRAME_CONTAINER "%s": dependency-injection bridge is unavailable.', $configured)
            );
        }

        if (!class_exists($configured)) {
            throw new RuntimeException(sprintf(
                'Invalid PHRAME_CONTAINER "%s": class was not found.',
                $configured,
            ));
        }

        try {
            $container = new $configured();
        } catch (Throwable $exception) {
            throw new RuntimeException(sprintf(
                'Invalid PHRAME_CONTAINER "%s": class could not be instantiated (%s).',
                $configured,
                $exception->getMessage(),
            ), (int) $exception->getCode(), $exception);
        }

        if (!$container instanceof ContainerBuilderInterface) {
            throw new RuntimeException(sprintf(
                'Invalid PHRAME_CONTAINER "%s": class must implement %s.',
                $configured,
                ContainerBuilderInterface::class,
            ));
        }

        return $container;
    }

    private static function createPreferredContainer(): ?ContainerBuilderInterface
    {
        foreach (self::PREFERRED_CONTAINER_CANDIDATES as $containerClass) {
            if (!class_exists($containerClass)) {
                continue;
            }

            if (!is_subclass_of($containerClass, ContainerBuilderInterface::class)) {
                continue;
            }

            if (!method_exists($containerClass, 'isAvailable')) {
                continue;
            }

            if (!$containerClass::isAvailable()) {
                continue;
            }

            return new $containerClass();
        }

        return null;
    }
}
