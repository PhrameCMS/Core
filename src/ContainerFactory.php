<?php

declare(strict_types=1);

namespace PhrameCMS\Core;

use PhrameCMS\Core\Contracts\ContainerBuilderInterface;
use RuntimeException;
use Throwable;

final class ContainerFactory
{
    private const SYMFONY_ADAPTER_CLASS = 'PhrameCMS\\Core\\SymfonyContainer';

    public static function createDefault(): ContainerBuilderInterface
    {
        $configured = trim((string) (getenv('PHRAME_CONTAINER') ?: ''));

        if ($configured !== '') {
            return self::fromConfiguration($configured);
        }

        if (self::isSymfonyAdapterAvailable()) {
            return self::createSymfonyAdapter();
        }

        return new CoreContainer();
    }

    private static function fromConfiguration(string $configured): ContainerBuilderInterface
    {
        $engine = ContainerEngine::fromConfiguration($configured);

        if ($engine === ContainerEngine::Native) {
            return new CoreContainer();
        }

        if ($engine === ContainerEngine::Symfony) {
            if (self::isSymfonyAdapterAvailable()) {
                return self::createSymfonyAdapter();
            }

            throw new RuntimeException(
                'Invalid PHRAME_CONTAINER "symfony": Symfony DependencyInjection adapter is unavailable.'
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

    private static function isSymfonyAdapterAvailable(): bool
    {
        $adapterClass = self::SYMFONY_ADAPTER_CLASS;

        return class_exists($adapterClass)
            && method_exists($adapterClass, 'isAvailable')
            && $adapterClass::isAvailable();
    }

    private static function createSymfonyAdapter(): ContainerBuilderInterface
    {
        $adapterClass = self::SYMFONY_ADAPTER_CLASS;
        $adapter = new $adapterClass();

        if (!$adapter instanceof ContainerBuilderInterface) {
            throw new RuntimeException(sprintf(
                'Invalid Symfony adapter "%s": class must implement %s.',
                $adapterClass,
                ContainerBuilderInterface::class,
            ));
        }

        return $adapter;
    }
}
